<?php

namespace App\Http\Controllers\Api\MonitoringAktif;

use App\Http\Controllers\Controller;
use App\Models\Armada;
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
 * @group Import Monitoring
 *
 * APIs for importing monitoring activation data via Excel.
 */
class MonitoringImportController extends Controller
{
    /**
     * Download import template for Monitoring Aktif
     */
    public function template()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Monitoring');

        // --- Title ---
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT MONITORING KENDARAAN - RM TRANS');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3E3D90']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // --- Note ---
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', '* nopol & km_awal wajib. spedo_status: HIDUP atau MATI (default: HIDUP). keterangan opsional.');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF6B7280']],
        ]);

        // --- Headers ---
        $headers = ['nopol', 'km_awal', 'spedo_status', 'keterangan'];
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0891B2']],
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
        $examples = [
            ['B 1234 AB', 125000, 'HIDUP', 'Unit normal'],
            ['D 5678 CD', 87500, 'MATI', 'Speedometer rusak'],
            ['B 9999 ZZ', 210000, 'HIDUP', '-'],
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
            $sheet->setCellValue("D{$r}", $row[3]);
            $sheet->getStyle("A{$r}:D{$r}")->applyFromArray($dataStyle);
        }

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(30);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'Template_Import_Monitoring.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Preview and validate monitoring import data
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

            // Pre-load existing armadas and their active monitoring status
            $armadaMap = Armada::pluck('id', 'nopol')->mapWithKeys(
                fn ($id, $nopol) => [strtoupper(trim($nopol)) => $id]
            )->toArray();

            $activeMonitoringArmadaIds = MonitoringArmadaAktif::where('status', 'aktif')
                ->pluck('armada_id')
                ->toArray();

            $seenNopols = [];
            $previewData = [];
            $validCount = 0;
            $invalidCount = 0;

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $nopol = strtoupper(trim($row['nopol'] ?? ''));
                $kmAwal = trim($row['km_awal'] ?? '');
                $spedoStatus = strtoupper(trim($row['spedo_status'] ?? 'HIDUP'));
                $keterangan = trim($row['keterangan'] ?? '-');

                $errors = [];

                // Validate nopol
                if (empty($nopol)) {
                    $errors[] = 'Nopol wajib diisi';
                } elseif (! isset($armadaMap[$nopol])) {
                    $errors[] = "Nopol '{$nopol}' tidak ditemukan di database. Daftarkan armada terlebih dahulu.";
                } elseif (in_array($armadaMap[$nopol], $activeMonitoringArmadaIds)) {
                    $errors[] = "Armada '{$nopol}' sudah memiliki monitoring aktif.";
                } elseif (in_array($nopol, $seenNopols)) {
                    $errors[] = "Nopol '{$nopol}' duplikat dalam file ini.";
                }

                // Validate km_awal
                if ($kmAwal === '') {
                    $errors[] = 'KM awal wajib diisi';
                } elseif (! is_numeric($kmAwal) || (float) $kmAwal < 0) {
                    $errors[] = 'KM awal harus berupa angka ≥ 0';
                }

                // Validate spedo_status
                if (! in_array($spedoStatus, ['HIDUP', 'MATI'])) {
                    $errors[] = "Spedo status harus 'HIDUP' atau 'MATI', nilai saat ini: '{$spedoStatus}'";
                    $spedoStatus = 'HIDUP'; // reset to default for display
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
                        'km_awal' => $kmAwal,
                        'spedo_status' => $spedoStatus,
                        'keterangan' => $keterangan,
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
     * Execute monitoring import
     *
     * @bodyParam rows array required Array of valid monitoring rows.
     */
    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rows' => 'required|array|min:1',
            'rows.*.nopol' => 'required|string',
            'rows.*.km_awal' => 'required|numeric|min:0',
            'rows.*.spedo_status' => 'nullable|in:HIDUP,MATI',
            'rows.*.keterangan' => 'nullable|string',
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

                // Skip if already has active monitoring
                $alreadyMonitored = MonitoringArmadaAktif::where('armada_id', $armada->id)
                    ->where('status', 'aktif')
                    ->exists();

                if ($alreadyMonitored) {
                    $skippedCount++;

                    continue;
                }

                MonitoringArmadaAktif::create([
                    'armada_id' => $armada->id,
                    'last_recorded_km' => (int) $row['km_awal'],
                    'status' => 'aktif',
                    'spedo_status' => $row['spedo_status'] ?? 'HIDUP',
                    'tanggal_mulai_monitoring' => Carbon::now()->toDateString(),
                    'keterangan' => $row['keterangan'] ?? '-',
                ]);

                $savedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "{$savedCount} monitoring berhasil diaktifkan".($skippedCount > 0 ? ", {$skippedCount} dilewati." : '.'),
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
            $expectedHeaders = ['nopol', 'km_awal', 'spedo_status', 'keterangan'];

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 1; $col <= 4; $col++) {
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
