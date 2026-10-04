<?php

namespace App\Http\Controllers\Api\LogKilometer;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\LogKilometer;
use App\Models\MonitoringArmadaAktif;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Import Log Kilometer
 *
 * APIs for importing bulk odometer log data via Excel.
 */
class LogKilometerImportController extends Controller
{
    /**
     * Download import template for Log Kilometer
     */
    public function template()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Log KM');

        // --- Title ---
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT LOG KILOMETER - RM TRANS');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3E3D90']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // --- Note ---
        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', '* nopol & odometer_km wajib. tgl_input format: YYYY-MM-DD (kosong = hari ini). Odometer harus lebih besar dari KM terakhir.');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF6B7280']],
        ]);

        // --- Headers ---
        $headers = ['nopol', 'odometer_km', 'tgl_input'];
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
        ];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}3", $header);
            $sheet->getStyle("{$col}3")->applyFromArray($headerStyle);
        }
        $sheet->getRowDimension(3)->setRowHeight(22);

        // --- Examples ---
        $today = Carbon::now()->toDateString();
        $examples = [
            ['B 1234 AB', 126500, $today],
            ['D 5678 CD', 88200, $today],
            ['B 9999 ZZ', 215000, $today],
        ];
        $dataStyle = [
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']],
        ];
        foreach ($examples as $i => $row) {
            $r = $i + 4;
            $sheet->setCellValue("A{$r}", $row[0]);
            $sheet->setCellValue("B{$r}", $row[1]);
            $sheet->setCellValue("C{$r}", $row[2]);
            $sheet->getStyle("A{$r}:C{$r}")->applyFromArray($dataStyle);
        }

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'Template_Import_Log_KM.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Preview and validate Log KM import data
     *
     * @bodyParam file file required The Excel file to import.
     */
    public function preview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'File tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $rows = $this->parseExcelFile($request->file('file'));

            if (empty($rows)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File kosong atau tidak ada data yang dapat dibaca.',
                ], 422);
            }

            // Pre-load armada & monitoring data
            $armadaMap = Armada::pluck('id', 'nopol')->mapWithKeys(
                fn ($id, $nopol) => [strtoupper(trim($nopol)) => $id]
            )->toArray();

            $monitoringMap = MonitoringArmadaAktif::where('status', 'aktif')
                ->pluck('last_recorded_km', 'armada_id')
                ->toArray();

            $seenNopols = [];
            $previewData = [];
            $validCount = 0;
            $invalidCount = 0;

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $nopol = strtoupper(trim($row['nopol'] ?? ''));
                $odometerKm = trim($row['odometer_km'] ?? '');
                $tglInput = trim($row['tgl_input'] ?? '') ?: Carbon::now()->toDateString();

                $errors = [];
                $lastKm = null;

                // Validate nopol
                if (empty($nopol)) {
                    $errors[] = 'Nopol wajib diisi';
                } elseif (! isset($armadaMap[$nopol])) {
                    $errors[] = "Nopol '{$nopol}' tidak ditemukan di database";
                } else {
                    $armadaId = $armadaMap[$nopol];
                    if (! isset($monitoringMap[$armadaId])) {
                        $errors[] = "Armada '{$nopol}' tidak sedang dimonitoring (belum diaktifkan)";
                    } else {
                        $lastKm = $monitoringMap[$armadaId];
                    }
                }

                // Validate odometer_km
                if ($odometerKm === '') {
                    $errors[] = 'Odometer KM wajib diisi';
                } elseif (! is_numeric($odometerKm)) {
                    $errors[] = 'Odometer KM harus berupa angka';
                } elseif ($lastKm !== null && (int) $odometerKm <= $lastKm) {
                    $errors[] = "Odometer KM ({$odometerKm}) harus lebih besar dari KM terakhir ({$lastKm} km)";
                }

                // Validate tgl_input
                if (! empty($tglInput)) {
                    try {
                        $parsedDate = Carbon::parse($tglInput);
                        if ($parsedDate->isFuture()) {
                            $errors[] = 'Tanggal input tidak boleh tanggal yang akan datang';
                        }
                        $tglInput = $parsedDate->toDateString();
                    } catch (\Exception $e) {
                        $errors[] = "Format tanggal tidak valid: '{$tglInput}'. Gunakan format YYYY-MM-DD";
                    }
                }

                $status = empty($errors) ? 'valid' : 'invalid';
                if ($status === 'valid') {
                    $validCount++;
                    $seenNopols[] = $nopol;
                } else {
                    $invalidCount++;
                }

                $previewData[] = [
                    'row_number' => $rowNumber,
                    'status' => $status,
                    'content' => [
                        'nopol' => $nopol,
                        'odometer_km' => $odometerKm,
                        'tgl_input' => $tglInput,
                        'last_km' => $lastKm,
                    ],
                    'errors' => $errors,
                ];
            }

            return response()->json([
                'status' => 'success',
                'total_rows' => count($previewData),
                'valid_rows' => $validCount,
                'invalid_rows' => $invalidCount,
                'data' => $previewData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membaca file: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute Log KM import
     *
     * @bodyParam rows array required Array of valid log km rows.
     */
    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rows' => 'required|array|min:1',
            'rows.*.nopol' => 'required|string',
            'rows.*.odometer_km' => 'required|numeric|min:0',
            'rows.*.tgl_input' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $savedCount = 0;
            $skippedCount = 0;

            foreach ($request->rows as $row) {
                $nopol = strtoupper(trim($row['nopol']));
                $armada = Armada::where('nopol', $nopol)->first();

                if (! $armada) {
                    $skippedCount++;

                    continue;
                }

                $monitoring = MonitoringArmadaAktif::where('armada_id', $armada->id)
                    ->where('status', 'aktif')
                    ->first();

                if (! $monitoring) {
                    $skippedCount++;

                    continue;
                }

                $newOdometer = (int) $row['odometer_km'];

                if ($newOdometer <= $monitoring->last_recorded_km) {
                    $skippedCount++;

                    continue;
                }

                $deltaKm = $newOdometer - $monitoring->last_recorded_km;
                $tglInput = ! empty($row['tgl_input']) ? Carbon::parse($row['tgl_input'])->toDateString() : Carbon::now()->toDateString();

                LogKilometer::create([
                    'armada_id' => $armada->id,
                    'odometer_km' => $newOdometer,
                    'delta_km' => $deltaKm,
                    'tgl_input' => $tglInput,
                ]);

                $monitoring->update(['last_recorded_km' => $newOdometer]);

                $savedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "{$savedCount} log kilometer berhasil diimport".($skippedCount > 0 ? ", {$skippedCount} dilewati." : '.'),
                'saved_count' => $savedCount,
                'skipped_count' => $skippedCount,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse Excel/CSV file into array of rows.
     *
     * @return array<int, array<string, string>>
     */
    private function parseExcelFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if ($extension === 'csv') {
            $handle = fopen($file->getRealPath(), 'r');
            $headers = null;
            while (($line = fgetcsv($handle)) !== false) {
                if ($headers === null) {
                    $headers = array_map('trim', $line);

                    continue;
                }
                $combined = array_combine($headers, array_pad($line, count($headers), ''));
                if (! empty(array_filter($combined))) {
                    $rows[] = $combined;
                }
            }
            fclose($handle);
        } else {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $headers = null;

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 1; $col <= 3; $col++) {
                    $rowData[] = (string) $sheet->getCellByColumnAndRow($col, $row)->getValue();
                }

                $normalized = array_map(fn ($h) => strtolower(trim($h)), $rowData);
                if (in_array('nopol', $normalized)) {
                    $headers = $normalized;

                    continue;
                }

                if ($headers === null) {
                    continue;
                }

                $combined = array_combine($headers, array_pad($rowData, count($headers), ''));
                if (! empty(array_filter($combined))) {
                    $rows[] = $combined;
                }
            }
        }

        return $rows;
    }
}
