<?php

namespace App\Http\Controllers\Api\Armada;

use App\Http\Controllers\Controller;
use App\Models\Armada;
use App\Models\JenisArmada;
use App\Models\MerkArmada;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Import Armada
 *
 * APIs for importing vehicle fleet (Armada) data via Excel.
 */
class ArmadaImportController extends Controller
{
    /**
     * Download import template for Armada
     *
     * Returns an Excel file (.xlsx) template with headers and example data.
     */
    public function template()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Armada');

        // --- Title ---
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA ARMADA - RM TRANS');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3E3D90']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // --- Sub note ---
        $sheet->mergeCells('A2:C2');
        $sheet->setCellValue('A2', '* Kolom nopol, nama_merk, nama_jenis wajib diisi. Merk & Jenis yang belum ada akan dibuat otomatis.');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // --- Headers ---
        $headers = ['nopol', 'nama_merk', 'nama_jenis'];
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
        ];
        foreach ($headers as $colIndex => $header) {
            $col = chr(65 + $colIndex);
            $sheet->setCellValue("{$col}3", $header);
            $sheet->getStyle("{$col}3")->applyFromArray($headerStyle);
        }
        $sheet->getRowDimension(3)->setRowHeight(22);

        // --- Example rows ---
        $examples = [
            ['B 1234 AB', 'HINO', 'BUS BESAR'],
            ['D 5678 CD', 'MITSUBISHI', 'TRUK ENGKEL'],
            ['B 9999 ZZ', 'MERCEDES', 'BUS SEDANG'],
        ];
        $dataStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']],
        ];
        foreach ($examples as $rowIndex => $row) {
            $excelRow = $rowIndex + 4;
            $sheet->setCellValue("A{$excelRow}", $row[0]);
            $sheet->setCellValue("B{$excelRow}", $row[1]);
            $sheet->setCellValue("C{$excelRow}", $row[2]);
            $sheet->getStyle("A{$excelRow}:C{$excelRow}")->applyFromArray($dataStyle);
        }

        // --- Column widths ---
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_Import_Armada.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Preview and validate import data
     *
     * Parse the uploaded Excel file and validate each row without saving to database.
     *
     * @bodyParam file file required The Excel file (.xlsx or .csv) to import.
     *
     * @response {
     *   "status": "success",
     *   "total_rows": 3,
     *   "valid_rows": 2,
     *   "invalid_rows": 1,
     *   "data": [...]
     * }
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

            // Collect existing nopols in DB for quick lookup
            $existingNopols = Armada::pluck('nopol')->map(fn ($n) => strtoupper(trim($n)))->toArray();

            // Track duplicates within the file itself
            $seenNopols = [];
            $previewData = [];
            $validCount = 0;
            $invalidCount = 0;

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Excel row (header is row 1)
                $nopol = strtoupper(trim($row['nopol'] ?? ''));
                $namaMerk = trim($row['nama_merk'] ?? '');
                $namaJenis = trim($row['nama_jenis'] ?? '');

                $errors = [];

                // Validate nopol
                if (empty($nopol)) {
                    $errors[] = 'Nopol wajib diisi';
                } elseif (in_array($nopol, $existingNopols)) {
                    $errors[] = "Nopol '{$nopol}' sudah terdaftar di sistem";
                } elseif (in_array($nopol, $seenNopols)) {
                    $errors[] = "Nopol '{$nopol}' duplikat dalam file ini";
                }

                // Validate nama_merk
                if (empty($namaMerk)) {
                    $errors[] = 'Nama merk wajib diisi';
                }

                // Validate nama_jenis
                if (empty($namaJenis)) {
                    $errors[] = 'Nama jenis wajib diisi';
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
                        'nama_merk' => $namaMerk,
                        'nama_jenis' => $namaJenis,
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
     * Execute the import
     *
     * Save the valid rows to the database using a DB transaction.
     *
     * @bodyParam rows array required Array of valid row data.
     * @bodyParam mode string Optional Import mode: 'strict' (default) or 'partial'.
     */
    public function execute(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rows' => 'required|array|min:1',
            'rows.*.nopol' => 'required|string',
            'rows.*.nama_merk' => 'required|string',
            'rows.*.nama_jenis' => 'required|string',
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

                // Skip if nopol already exists (double-check safety)
                if (Armada::where('nopol', $nopol)->exists()) {
                    $skippedCount++;

                    continue;
                }

                // Auto-create or find merk
                $merk = MerkArmada::firstOrCreate(
                    ['nama_merk' => trim($row['nama_merk'])],
                    ['nama_merk' => trim($row['nama_merk'])]
                );

                // Auto-create or find jenis
                $jenis = JenisArmada::firstOrCreate(
                    ['nama_jenis' => trim($row['nama_jenis'])],
                    ['nama_jenis' => trim($row['nama_jenis'])]
                );

                Armada::create([
                    'nopol' => $nopol,
                    'merk_armada_id' => $merk->id,
                    'jenis_armada_id' => $jenis->id,
                ]);

                $savedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "{$savedCount} data armada berhasil diimport".($skippedCount > 0 ? ", {$skippedCount} dilewati (sudah ada)." : '.'),
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

                if ($row === 1) {
                    // Look for header row (contains 'nopol')
                    if (in_array('nopol', array_map('strtolower', array_map('trim', $rowData)))) {
                        $headers = array_map(fn ($h) => strtolower(trim($h)), $rowData);
                    }

                    continue;
                }

                // If headers found (handles title row before headers)
                if ($headers === null) {
                    // Try this row as header
                    if (in_array('nopol', array_map('strtolower', array_map('trim', $rowData)))) {
                        $headers = array_map(fn ($h) => strtolower(trim($h)), $rowData);
                    }

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
