<?php

namespace App\Http\Controllers\Dashboard\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Siswa;
use App\Services\DocumentGeneratorService;
use App\Services\TemplateVariableRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use ZipArchive;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentGeneratorService $generatorService
    ) {}

    // =========================================================================
    // INDEX
    // =========================================================================

    public function index(): View
    {
        $documents = Document::with('template.category', 'siswa')
            ->latest()
            ->paginate(20);

        return view('dashboard.document.documents.index', compact('documents'));
    }

    // =========================================================================
    // CREATE / STORE (single generate)
    // =========================================================================

    public function create(DocumentTemplate $template): View
    {
        $template->load('category');
        $variables      = $template->extractVariables();
        $variableGroups = TemplateVariableRegistry::getGrouped();

        return view('dashboard.document.documents.create', compact('template', 'variables', 'variableGroups'));
    }

    public function store(Request $request, DocumentTemplate $template): Response
    {
        $template->load('category');
        $variables = $template->extractVariables();

        $rules = [];
        foreach ($variables as $var) {
            $rules[$var] = 'nullable|string';
        }

        $userData = $request->validate($rules);
        $meta     = [];

        $document   = $this->generatorService->generate($template, $userData, $meta);
        $pdfContent = \Storage::disk('public')->get($document->file_path);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="dokumen-' . $document->verification_code . '.pdf"',
        ]);
    }

    // =========================================================================
    // EXCEL TEMPLATE DOWNLOAD
    // =========================================================================

    public function excelTemplate(DocumentTemplate $template): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $template->load('category');
        $variables = $template->extractVariables();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $isRaport      = strtolower($template->category?->name ?? '') === 'rapot';
        $prefixColumns = $isRaport ? ['nisn' => 'NISN', 'nama_siswa' => 'Nama Siswa'] : [];

        // Gabungkan prefix + variabel template (hindari duplikat)
        $allColumns = [];
        foreach ($prefixColumns as $key => $label) {
            $allColumns[$key] = $label;
        }
        foreach ($variables as $var) {
            if (!array_key_exists($var, $allColumns)) {
                $allColumns[$var] = $this->friendlyVarLabel($var);
            }
        }

        $totalCols = count($allColumns);
        $lastCol   = Coordinate::stringFromColumnIndex($totalCols);

        // ── WARNA TEMA ────────────────────────────────────────────────────────
        $colorHeader       = 'FF1A5276';
        $colorHeaderRaport = 'FF154360';
        $colorSubHeader    = 'FF2980B9';
        $colorRowOdd       = 'FFF0F7FF';
        $colorRowEven      = 'FFFFFFFF';
        $colorHidden       = 'FFFFFFFF';
        $colorExample      = 'FFFFFDE7';

        // ── Deteksi apakah ada tabel mode daftar di canvas ────────────────────
        $tableModeMap   = $this->extractTableModeMap($template);
        $hasDaftarTable = in_array('daftar', array_values($tableModeMap), true);

        // ── BARIS 1: JUDUL TEMPLATE ───────────────────────────────────────────
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $modeLabel = $hasDaftarTable
            ? 'Mode: Daftar (ada tabel daftar — semua baris → 1 PDF)'
            : 'Mode: Per Orang (1 baris → 1 PDF)';
        $sheet->setCellValue('A1', '📋  Template: ' . $template->name
            . '   |   Kategori: ' . ($template->category?->name ?? '-')
            . '   |   ' . $modeLabel
            . '   |   ' . now()->format('d M Y'));

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 11,
                'name'  => 'Arial',
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => $colorSubHeader],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'indent'     => 1,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── BARIS 2: PANDUAN SINGKAT ──────────────────────────────────────────
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $guideText = $hasDaftarTable
            ? '⚠  Mode DAFTAR: Isi semua data mulai baris ke-5. Semua baris akan digabung menjadi 1 PDF. Jangan ubah baris 3 dan 4.'
            : '⚠  Mode PER ORANG: Isi data mulai baris ke-5. Setiap baris = 1 dokumen PDF terpisah. Jangan ubah baris 3 dan 4.';
        $sheet->setCellValue('A2', $guideText);

        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 9,
                'name'  => 'Arial',
                'color' => ['argb' => 'FF7D3C00'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFF3CD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'indent'     => 1,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['argb' => 'FFCC8A00'],
                ],
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // ── BARIS 3: HEADER LABEL ─────────────────────────────────────────────
        $colIndex = 1;
        foreach ($allColumns as $varKey => $label) {
            $cell     = Coordinate::stringFromColumnIndex($colIndex) . '3';
            $isPrefix = $isRaport && isset($prefixColumns[$varKey]);
            $bgColor  = $isPrefix ? $colorHeaderRaport : $colorHeader;

            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'size'  => 10,
                    'name'  => 'Arial',
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $bgColor],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF5D8AA8'],
                    ],
                ],
            ]);

            $sheet->getColumnDimensionByColumn($colIndex)
                  ->setWidth(max(16, min(38, mb_strlen($label) + 6)));

            $colIndex++;
        }
        $sheet->getRowDimension(3)->setRowHeight(26);

        // ── BARIS 4: HIDDEN VARIABLE ROW (untuk mapping saat import) ─────────
        $colIndex = 1;
        foreach ($allColumns as $varKey => $label) {
            $cell = Coordinate::stringFromColumnIndex($colIndex) . '4';
            $sheet->setCellValue($cell, '__VAR__' . $varKey);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'size'  => 7,
                    'name'  => 'Arial',
                    'color' => ['argb' => $colorHidden],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $colorHidden],
                ],
            ]);
            $colIndex++;
        }
        $sheet->getRowDimension(4)->setRowHeight(3);

        // ── BARIS 5: CONTOH DATA ──────────────────────────────────────────────
        $colIndex = 1;
        foreach ($allColumns as $varKey => $label) {
            $cell    = Coordinate::stringFromColumnIndex($colIndex) . '5';
            $example = $this->getExampleValue($varKey);

            $sheet->setCellValue($cell, $example);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'italic' => true,
                    'size'   => 9,
                    'name'   => 'Arial',
                    'color'  => ['argb' => 'FF7F8C8D'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $colorExample],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'indent'     => 1,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_HAIR,
                        'color'       => ['argb' => 'FFBDC3C7'],
                    ],
                ],
            ]);
            $colIndex++;
        }
        $sheet->getRowDimension(5)->setRowHeight(18);

        // ── BARIS 6+: AREA DATA KOSONG (zebra striping 20 baris) ─────────────
        for ($row = 6; $row <= 25; $row++) {
            $bgColor  = ($row % 2 === 0) ? $colorRowEven : $colorRowOdd;
            $colIndex = 1;
            foreach ($allColumns as $varKey => $label) {
                $cell = Coordinate::stringFromColumnIndex($colIndex) . $row;
                $sheet->getStyle($cell)->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => $bgColor],
                    ],
                    'font' => [
                        'size' => 10,
                        'name' => 'Arial',
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'indent'   => 1,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_HAIR,
                            'color'       => ['argb' => 'FFBDC3C7'],
                        ],
                    ],
                ]);
                $colIndex++;
            }
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        // ── BORDER & FREEZE ───────────────────────────────────────────────────
        $sheet->getStyle('A3:' . $lastCol . '3')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['argb' => 'FF1A5276'],
                ],
            ],
        ]);
        $sheet->getStyle('A5:' . $lastCol . '25')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['argb' => 'FF2980B9'],
                ],
            ],
        ]);
        $sheet->freezePane('A5');

        if ($isRaport) {
            $sheet->getColumnDimension('A')->setWidth(18);
            $sheet->getColumnDimension('B')->setWidth(28);
        }

        // =========================================================================
        // SHEET 2: PETUNJUK PENGISIAN
        // =========================================================================

        $info = $spreadsheet->createSheet();
        $info->setTitle('Petunjuk');

        $info->mergeCells('A1:C1');
        $info->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE');
        $info->getStyle('A1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 14,
                'name'  => 'Arial',
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => $colorHeader],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $info->getRowDimension(1)->setRowHeight(32);

        // Daftar tabel & mode masing-masing
        $tableModeDisplay = empty($tableModeMap)
            ? 'Tidak ada tabel (Per Orang)'
            : implode(', ', array_map(
                fn($id, $mode) => $id . ': ' . ($mode === 'daftar' ? 'Daftar' : 'Per Orang'),
                array_keys($tableModeMap),
                array_values($tableModeMap)
              ));

        $infoMeta = [
            ['Template',      $template->name],
            ['Kategori',      $template->category?->name ?? '-'],
            ['Mode Utama',    $hasDaftarTable ? 'Daftar — semua baris → 1 PDF' : 'Per Orang — 1 baris → 1 PDF'],
            ['Detail Tabel',  $tableModeDisplay],
            ['Variabel',      count($allColumns) . ' kolom'],
            ['Dibuat',        now()->format('d M Y H:i')],
        ];

        $row = 3;
        foreach ($infoMeta as [$key, $val]) {
            $info->setCellValue('A' . $row, $key);
            $info->setCellValue('B' . $row, $val);
            $info->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F7FF']],
            ]);
            $info->getStyle('B' . $row)->getFont()->setName('Arial')->setSize(10);
            $row++;
        }

        $steps = $hasDaftarTable ? [
            ['no' => '1', 'step' => 'Buka sheet "Data"'],
            ['no' => '2', 'step' => 'Isi data mulai dari BARIS KE-5 (baris kuning adalah contoh, boleh ditimpa)'],
            ['no' => '3', 'step' => 'SEMUA baris akan digabung menjadi 1 PDF — cocok untuk daftar hadir, rekap kelas, dll.'],
            ['no' => '4', 'step' => 'JANGAN mengubah atau menghapus baris 3 (header biru)'],
            ['no' => '5', 'step' => 'JANGAN mengubah atau menghapus baris 4 (baris sangat tipis — kode sistem)'],
            ['no' => '6', 'step' => 'Simpan file, lalu upload kembali di halaman Generate Dokumen'],
        ] : [
            ['no' => '1', 'step' => 'Buka sheet "Data"'],
            ['no' => '2', 'step' => 'Isi data mulai dari BARIS KE-5 (baris kuning adalah contoh, boleh ditimpa)'],
            ['no' => '3', 'step' => 'Setiap baris = satu dokumen PDF yang akan digenerate secara terpisah'],
            ['no' => '4', 'step' => 'JANGAN mengubah atau menghapus baris 3 (header biru)'],
            ['no' => '5', 'step' => 'JANGAN mengubah atau menghapus baris 4 (baris sangat tipis — kode sistem)'],
            ['no' => '6', 'step' => 'Simpan file, lalu upload kembali di halaman Generate Dokumen'],
        ];

        $row = $row + 1;
        $info->setCellValue('A' . $row, 'LANGKAH-LANGKAH:');
        $info->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FF1A5276']],
        ]);
        $info->getRowDimension($row)->setRowHeight(20);
        $row++;

        foreach ($steps as $step) {
            $info->setCellValue('A' . $row, $step['no'] . '.');
            $info->setCellValue('B' . $row, $step['step']);
            $info->getStyle('A' . $row)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => ['argb' => $colorHeader]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $info->getStyle('B' . $row)->applyFromArray([
                'font'      => ['size' => 10, 'name' => 'Arial'],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $info->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        if ($isRaport) {
            $row++;
            $info->setCellValue('A' . $row, '⚠ CATATAN KHUSUS RAPORT:');
            $info->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FFC0392B']],
            ]);
            $row++;
            foreach ([
                'Kolom NISN dan Nama Siswa WAJIB diisi untuk setiap baris.',
                'NISN harus sesuai dengan data siswa di sistem.',
                'Kolom nilai diisi sesuai nama variabel masing-masing mata pelajaran.',
                'Pastikan format nilai sesuai (angka/huruf tergantung kurikulum).',
            ] as $note) {
                $info->setCellValue('B' . $row, '• ' . $note);
                $info->getStyle('B' . $row)->applyFromArray([
                    'font'      => ['size' => 10, 'name' => 'Arial', 'color' => ['argb' => 'FFC0392B'], 'bold' => true],
                    'alignment' => ['wrapText' => true],
                ]);
                $info->getRowDimension($row)->setRowHeight(18);
                $row++;
            }
        }

        // Daftar variabel
        $row++;
        $info->setCellValue('A' . $row, 'DAFTAR KOLOM TEMPLATE INI:');
        $info->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FF1A5276']],
        ]);
        $row++;

        $info->setCellValue('A' . $row, 'No');
        $info->setCellValue('B' . $row, 'Nama Kolom (Label)');
        $info->setCellValue('C' . $row, 'Kode Variabel');
        $info->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorSubHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $info->getRowDimension($row)->setRowHeight(20);
        $row++;

        $no = 1;
        foreach ($allColumns as $varKey => $label) {
            $bgColor = ($no % 2 === 0) ? 'FFFAFAFA' : 'FFF0F7FF';
            $info->setCellValue('A' . $row, $no);
            $info->setCellValue('B' . $row, $label);
            $info->setCellValue('C' . $row, '{{' . $varKey . '}}');
            $info->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                'font'      => ['size' => 10, 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => 'FFBDC3C7']]],
            ]);
            $info->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $info->getStyle('C' . $row)->getFont()->setName('Courier New')->setSize(9);
            $info->getStyle('C' . $row)->getFont()->getColor()->setARGB('FF2980B9');
            $info->getRowDimension($row)->setRowHeight(18);
            $no++;
            $row++;
        }

        $info->getColumnDimension('A')->setWidth(6);
        $info->getColumnDimension('B')->setWidth(30);
        $info->getColumnDimension('C')->setWidth(28);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'template-' . Str::slug($template->name) . '-' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new XlsxWriter($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // =========================================================================
    // PARSE EXCEL
    // =========================================================================

    public function parseExcel(Request $request, DocumentTemplate $template): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $template->load('category');
        $variables = $template->extractVariables();

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(
            $request->file('excel_file')->getRealPath()
        );
        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return response()->json(['rows' => []]);
        }

        array_shift($rows); // baris 1: judul
        array_shift($rows); // baris 2: panduan
        $headerRow = array_shift($rows); // baris 3: label
        $hiddenRow = !empty($rows) ? array_shift($rows) : []; // baris 4: __VAR__

        $colToVar      = $this->buildColToVarMap($headerRow, $hiddenRow, $variables);
        $usePositional = empty($colToVar);
        $colKeys       = array_keys($headerRow);

        $result = [];
        $index  = 1;

        foreach ($rows as $row) {
            $values = array_filter(array_values($row), fn($v) => $v !== null && $v !== '');
            if (empty($values)) continue;

            $firstVal = trim((string) (array_values($row)[0] ?? ''));
            if (str_starts_with($firstVal, 'Contoh ')) continue;

            $data = [];

            if ($usePositional) {
                foreach ($variables as $varIndex => $varName) {
                    $colKey         = $colKeys[$varIndex] ?? null;
                    $data[$varName] = $colKey && isset($row[$colKey])
                        ? $this->sanitizeCellValue($row[$colKey])
                        : '';
                }
            } else {
                foreach ($colToVar as $col => $varName) {
                    $data[$varName] = isset($row[$col])
                        ? $this->sanitizeCellValue($row[$col])
                        : '';
                }
                foreach ($variables as $varName) {
                    if (!array_key_exists($varName, $data)) {
                        $data[$varName] = '';
                    }
                }
            }

            $firstData = trim((string) (array_values($data)[0] ?? ''));
            $label     = $firstData ?: 'Baris ' . $index;
            $filename  = Str::slug($label) ?: 'dokumen-' . $index;

            $result[] = [
                'label'    => $label,
                'filename' => $filename,
                'data'     => $data,
            ];

            $index++;
        }

        // Sertakan info mode per tabel agar frontend bisa tampilkan keterangan
        $tableModeMap = $this->extractTableModeMap($template);

        return response()->json([
            'rows'            => $result,
            'total'           => count($result),
            'mapping_method'  => $usePositional ? 'positional' : 'mapped',
            'variables_found' => array_values($colToVar),
            'table_modes'     => $tableModeMap,
            'has_daftar'      => in_array('daftar', array_values($tableModeMap), true),
        ]);
    }

    // =========================================================================
    // BATCH GENERATE (ZIP download)
    // =========================================================================

    public function batchGenerate(Request $request, DocumentTemplate $template): Response
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $template->load('category');
        $variables = $template->extractVariables();

        $isRaport          = strtolower($template->category?->name ?? '') === 'rapot';
        $allKnownVariables = $isRaport
            ? array_merge(['nisn', 'nama_siswa'], $variables)
            : $variables;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(
            $request->file('excel_file')->getRealPath()
        );
        $sheet = $spreadsheet->getActiveSheet();
        $rows  = $sheet->toArray(null, true, true, true);

        array_shift($rows); // baris 1: judul
        array_shift($rows); // baris 2: panduan
        $headerRow     = array_shift($rows); // baris 3: label
        $hiddenRow     = !empty($rows) ? array_shift($rows) : []; // baris 4: __VAR__
        $colToVar      = $this->buildColToVarMap($headerRow, $hiddenRow, $allKnownVariables);
        $usePositional = empty($colToVar);
        $colKeys       = array_keys($headerRow);

        // Filter baris kosong & contoh
        $dataRows = [];
        foreach ($rows as $rowNum => $row) {
            $values = array_filter(array_values($row), fn($v) => $v !== null && $v !== '');
            if (empty($values)) continue;
            $firstVal = trim((string) (array_values($row)[0] ?? ''));
            if (str_starts_with($firstVal, 'Contoh ')) continue;
            $dataRows[$rowNum] = $row;
        }

        // ══════════════════════════════════════════════════════════════════════
        // DETEKSI KOLOM DAFTAR — 3 lapis prioritas
        //
        // [1] canvas_json: tabel dengan table_mode = 'daftar' (paling akurat)
        // [2] canvas_json: variabel bernomor {{nama_1}}, {{nama_2}} di canvas
        // [3] html_template: variabel yang muncul ≥2 kali di baris berbeda
        //     dalam satu tabel → ini yang cover template lama seperti kasus ini
        //
        // Template di DB punya tbl_2 dengan {{nama_lengkap}} di 5 baris = daftar
        // ══════════════════════════════════════════════════════════════════════
        $daftarBaseVars = $this->extractDaftarVarNames($template);

        if (empty($daftarBaseVars)) {
            $daftarBaseVars = $this->detectListVarsFromTemplate($template, $colToVar);
        }

        if (empty($daftarBaseVars)) {
            $daftarBaseVars = $this->detectListVarsFromHtml($template, $colToVar);
        }

        $hasDaftarTable = !empty($daftarBaseVars);
        $varToCol        = array_flip($colToVar);

        // ══════════════════════════════════════════════════════════════════════
        // LANGKAH 1: Kumpulkan sharedData dari kolom daftar
        //
        // Loop SEMUA baris Excel, kumpulkan nilai kolom daftar jadi bernomor:
        //   nama_lengkap_1 = 'Ahmad Fauzi Ramadhan'   ← baris 1
        //   nama_lengkap_2 = 'Siti Aisyah Putri'       ← baris 2
        //   nama_lengkap_3 = 'Muhammad Rizki Aditya'   ← baris 3
        //   ... dst (semua baris, untuk semua PDF)
        //
        // sharedData ini di-inject ke SETIAP PDF yang digenerate
        // ══════════════════════════════════════════════════════════════════════
        $sharedData = [];

        // 

        if ($hasDaftarTable) {
            $listIdx = 1;
            foreach ($dataRows as $row) {
                foreach ($daftarBaseVars as $baseVar) {
                    // Cari kolom Excel — bisa jadi __VAR__nama_lengkap atau __VAR__nama_lengkap_1
                    $colKey = $varToCol[$baseVar] ?? null;
                    if (!$colKey) {
                        // Coba cari kolom yang base-nya cocok
                        foreach ($colToVar as $col => $mappedVar) {
                            if (preg_replace('/_\d+$/', '', $mappedVar) === $baseVar) {
                                $colKey = $col;
                                break;
                            }
                        }
                    }
                    $sharedData[$baseVar . '_' . $listIdx] = ($colKey && isset($row[$colKey]))
                        ? $this->sanitizeCellValue($row[$colKey])
                        : '';
                }
                $listIdx++;
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // LANGKAH 2: Generate PDF
        //
        // Setiap baris Excel = 1 PDF berisi:
        //   - Variabel per-orang dari baris itu (nilai, capaian, dsb.)
        //   - sharedData dari semua baris (daftar nama lengkap, sama di tiap PDF)
        // ══════════════════════════════════════════════════════════════════════
        // ══════════════════════════════════════════════════════════════════════
        // REBUILD html_template: sesuaikan jumlah baris tabel daftar
        // dengan jumlah data aktual dari Excel.
        //
        // Template asli punya N baris fixed (misal 5), tapi data bisa 15.
        // Method rebuildDaftarTablesInHtml() akan:
        //   - Hapus semua baris data di tabel daftar
        //   - Generate ulang baris sebanyak count($dataRows)
        //   - Setiap baris pakai {{daftar_1}}, {{daftar_2}}, ... sesuai urutan
        // ══════════════════════════════════════════════════════════════════════
        $dynamicTemplate = null;
        if ($hasDaftarTable && !empty($daftarBaseVars)) {
            $rebuiltHtml = $this->rebuildDaftarTablesInHtml(
                $template->html_template ?? '',
                $daftarBaseVars,
                count($dataRows)
            );
            if ($rebuiltHtml !== ($template->html_template ?? '')) {
                // Buat salinan template dengan html_template yang sudah di-rebuild
                $dynamicTemplate = clone $template;
                $dynamicTemplate->html_template = $rebuiltHtml;
            }
        }
        $activeTemplate = $dynamicTemplate ?? $template;

        $zipPath = sys_get_temp_dir() . '/batch_' . uniqid() . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $count = 0;

        // Cek apakah ada variabel per-orang (kolom selain daftar)
        $hasPerOrangVars = false;
        foreach ($colToVar as $col => $varName) {
            $base = preg_replace('/_\d+$/', '', $varName);
            if (!in_array($base, $daftarBaseVars)) {
                $hasPerOrangVars = true;
                break;
            }
        }

        // Template hanya berisi kolom daftar → 1 PDF saja
        if ($hasDaftarTable && !$hasPerOrangVars) {
            try {
                $doc        = $this->generatorService->generate($activeTemplate, $sharedData, []);
                $pdfContent = \Storage::disk('public')->get($doc->file_path);
                $zip->addFromString('daftar-' . Str::slug($template->name) . '.pdf', $pdfContent);
                $count = 1;
            } catch (\Throwable $e) {
                \Log::warning('Daftar-only generate failed: ' . $e->getMessage());
            }

        // Hybrid atau murni per-orang → tiap baris = 1 PDF
        } else {
            foreach ($dataRows as $rowNum => $row) {
                $perOrangData = [];

                if ($usePositional) {
                    foreach ($allKnownVariables as $varIdx => $varName) {
                        $base = preg_replace('/_\d+$/', '', $varName);
                        if (in_array($base, $daftarBaseVars)) continue;
                        $colKey                 = $colKeys[$varIdx] ?? null;
                        $perOrangData[$varName] = $colKey && isset($row[$colKey])
                            ? $this->sanitizeCellValue($row[$colKey])
                            : '';
                    }
                } else {
                    foreach ($colToVar as $col => $varName) {
                        $base = preg_replace('/_\d+$/', '', $varName);
                        if (in_array($base, $daftarBaseVars)) continue;
                        $perOrangData[$varName] = isset($row[$col])
                            ? $this->sanitizeCellValue($row[$col])
                            : '';
                    }
                    foreach ($allKnownVariables as $varName) {
                        $base = preg_replace('/_\d+$/', '', $varName);
                        if (in_array($base, $daftarBaseVars)) continue;
                        if (!array_key_exists($varName, $perOrangData)) {
                            $perOrangData[$varName] = '';
                        }
                    }
                }

                // Gabungkan: per-orang (unik tiap baris) + sharedData (sama semua PDF)
                $userData = array_merge($perOrangData, $sharedData);

                try {
                    $doc        = $this->generatorService->generate($activeTemplate, $userData, []);
                    $pdfContent = \Storage::disk('public')->get($doc->file_path);

                    if ($isRaport && !empty($userData['nama_siswa'])) {
                        $slug        = Str::slug($userData['nama_siswa']);
                        $nisn        = !empty($userData['nisn']) ? '-' . $userData['nisn'] : '';
                        $zipFileName = sprintf('%03d-%s%s.pdf', $count + 1, $slug, $nisn);
                    } else {
                        $firstVal = '';
                        foreach ($perOrangData as $v) {
                            if ($v !== '') { $firstVal = $v; break; }
                        }
                        $slug        = Str::slug($firstVal ?: ('dokumen-' . ($count + 1)));
                        $zipFileName = sprintf('%03d-%s.pdf', $count + 1, $slug ?: 'dokumen');
                    }

                    $zip->addFromString($zipFileName, $pdfContent);
                    $count++;
                } catch (\Throwable $e) {
                    \Log::warning("Batch generate row {$rowNum} failed: " . $e->getMessage());
                }
            }
        }

        $zip->close();

        if ($count === 0) {
            abort(422, 'Tidak ada baris data valid yang ditemukan di file Excel.');
        }

        $zipContents = file_get_contents($zipPath);
        @unlink($zipPath);

        return response($zipContents, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="batch-' . Str::slug($template->name) . '.zip"',
            'X-Batch-Total'       => $count,
        ]);
    }

    // =========================================================================
    // BULK CREATE (dari daftar siswa)
    // =========================================================================

    public function bulkCreate(): View
    {
        $templates = DocumentTemplate::with('category')->orderBy('name')->get();
        $classes   = Siswa::distinct()->orderBy('class')->pluck('class');

        return view('dashboard.document.documents.bulk-create', compact('templates', 'classes'));
    }

    public function bulkPreviewVariables(Request $request): \Illuminate\Http\JsonResponse
    {
        $template  = DocumentTemplate::findOrFail($request->template_id);
        $variables = $template->extractVariables();

        return response()->json(['variables' => $variables]);
    }

    public function bulkStore(Request $request): mixed
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'scope'       => 'required|in:class,all,selected',
            'output'      => 'required|in:separate,merged',
        ]);

        $template  = DocumentTemplate::with('category')->findOrFail($request->template_id);
        $variables = $template->extractVariables();

        $query = Siswa::query();

        if ($request->scope === 'class') {
            $request->validate(['class' => 'required|string']);
            $query->where('class', $request->class);
        } elseif ($request->scope === 'selected') {
            $request->validate(['student_ids' => 'required|array|min:1']);
            $query->whereIn('id', $request->student_ids);
        }

        $students = $query->orderBy('name')->get();

        if ($students->isEmpty()) {
            return back()->withErrors(['scope' => 'Tidak ada siswa yang ditemukan.']);
        }

        $fieldMap   = $request->input('field_map', []);
        $staticVars = collect($request->all())
            ->filter(fn($v, $k) => str_starts_with($k, 'static_'))
            ->mapWithKeys(fn($v, $k) => [str_replace('static_', '', $k) => $v]);

        $rows = $students->map(function (Siswa $student) use ($variables, $fieldMap, $staticVars) {
            $row = ['_student_id' => $student->id];

            foreach ($variables as $var) {
                if (isset($fieldMap[$var]) && $fieldMap[$var] !== '') {
                    $row[$var] = data_get($student, $fieldMap[$var]) ?? '';
                } elseif ($staticVars->has($var)) {
                    $row[$var] = $staticVars[$var];
                } else {
                    $row[$var] = '';
                }
            }

            return $row;
        });

        if ($request->output === 'merged') {
            $label      = 'rapot-' . ($request->class ?? 'Semua') . '-' . now()->format('Ymd');
            $filePath   = $this->generatorService->generateBulkMergedPdf($template, $rows, $label);
            $pdfContent = \Storage::disk('public')->get($filePath);

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $label . '.pdf"',
            ]);
        }

        $batchId = (string) Str::uuid();

        $this->generatorService->generateBulk(
            $template,
            $rows,
            function (int $done, int $total, Document $doc) use ($batchId) {
                $doc->update(['bulk_batch_id' => $batchId, 'created_by' => auth()->id()]);
            }
        );

        return redirect()
            ->route('dashboard.documents.index', ['batch' => $batchId])
            ->with('success', 'Berhasil generate ' . $students->count() . ' dokumen.');
    }

    // =========================================================================
    // BULK FROM CSV
    // =========================================================================

    public function bulkStoreFromCsv(Request $request): mixed
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'csv_file'    => 'required|file|mimes:csv,txt|max:2048',
            'output'      => 'required|in:separate,merged',
        ]);

        $template = DocumentTemplate::with('category')->findOrFail($request->template_id);

        $file    = $request->file('csv_file');
        $rows    = collect();
        $handle  = fopen($file->getPathname(), 'r');
        $headers = null;

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map('trim', $line);
                continue;
            }
            if (count($line) !== count($headers)) continue;
            $rows->push(array_combine($headers, array_map('trim', $line)));
        }

        fclose($handle);

        if ($rows->isEmpty()) {
            return back()->withErrors(['csv_file' => 'File CSV kosong atau format tidak valid.']);
        }

        if ($request->output === 'merged') {
            $label      = 'CSV-Bulk-' . now()->format('Ymd-His');
            $filePath   = $this->generatorService->generateBulkMergedPdf($template, $rows, $label);
            $pdfContent = \Storage::disk('public')->get($filePath);

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $label . '.pdf"',
            ]);
        }

        $batchId = (string) Str::uuid();

        $this->generatorService->generateBulk(
            $template,
            $rows,
            function (int $done, int $total, Document $doc) use ($batchId) {
                $doc->update(['bulk_batch_id' => $batchId, 'created_by' => auth()->id()]);
            }
        );

        return redirect()
            ->route('dashboard.documents.index', ['batch' => $batchId])
            ->with('success', 'Berhasil generate ' . $rows->count() . ' dokumen dari CSV.');
    }

    // =========================================================================
    // DOWNLOAD & DESTROY
    // =========================================================================

    public function download(Document $document): Response
    {
        abort_unless($document->fileExists(), 404, 'File PDF tidak ditemukan.');

        $pdfContent = \Storage::disk('public')->get($document->file_path);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="dokumen-' . $document->verification_code . '.pdf"',
        ]);
    }

    public function destroy(Document $document): RedirectResponse
    {
        if ($document->file_path && $document->fileExists()) {
            \Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('dashboard.documents.index')
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    // =========================================================================
    // SISWA HELPERS
    // =========================================================================

    public function searchSiswa(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = $request->input('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Siswa::query()
            ->where('name', 'like', "%{$q}%")
            ->orWhere('nisn', 'like', "%{$q}%")
            ->limit(15)
            ->get(['id', 'name', 'nisn', 'jk'])
            ->map(fn(Siswa $s) => [
                'id'   => $s->id,
                'text' => $s->name . ($s->nisn ? " ({$s->nisn})" : ''),
                'name' => $s->name,
                'nisn' => $s->nisn,
            ]);

        return response()->json($results);
    }

    public function getSiswaData(Siswa $siswa): \Illuminate\Http\JsonResponse
    {
        $data = TemplateVariableRegistry::mapSiswaData($siswa);
        $data = array_merge(TemplateVariableRegistry::getSekolahDefaults(), $data);

        return response()->json($data);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Rebuild tabel daftar di html_template agar jumlah barisnya sesuai
     * dengan jumlah data aktual dari Excel.
     *
     * Masalah: template dibuat dengan N baris fixed (misal 5), tapi data
     * bisa berisi 3, 10, atau 30 baris. Ini menyebabkan:
     *  - Kalau data < baris template → baris kosong di PDF
     *  - Kalau data > baris template → data terpotong, tidak semua muncul
     *
     * Solusi: parse html_template, cari tabel yang mengandung variabel daftar
     * ({{daftar_1}}, {{daftar_2}}, atau {{daftar}} berulang), lalu rebuild
     * tabel tersebut dengan jumlah baris = $totalRows.
     *
     * Warna zebra (background) dipertahankan sesuai pola asli.
     *
     * @param  string        $htmlTemplate   html_template dari DB
     * @param  array<string> $daftarBaseVars  ['daftar', 'nama_lengkap', ...]
     * @param  int           $totalRows       jumlah baris data aktual
     * @return string        html_template yang sudah di-rebuild
     */
    private function rebuildDaftarTablesInHtml(
        string $htmlTemplate,
        array  $daftarBaseVars,
        int    $totalRows
    ): string {
        if (empty($htmlTemplate) || empty($daftarBaseVars) || $totalRows <= 0) {
            return $htmlTemplate;
        }

        // Pattern variabel daftar untuk pencocokan
        $daftarVarPattern = implode('|', array_map('preg_quote', $daftarBaseVars));

        // Cari dan replace setiap <table> yang mengandung variabel daftar
        $result = preg_replace_callback(
            '/<table([^>]*)>(.*?)<\/table>/is',
            function (array $match) use ($daftarVarPattern, $totalRows) {
                $tableAttrs    = $match[1];
                $tableContent  = $match[2];

                // Cek apakah tabel ini mengandung variabel daftar
                if (!preg_match('/\{\{(' . $daftarVarPattern . ')(?:_\d+)?\}\}/i', $tableContent)) {
                    return $match[0]; // bukan tabel daftar, biarkan
                }

                // Ambil semua baris
                preg_match_all('/<tr([^>]*)>(.*?)<\/tr>/is', $tableContent, $rowMatches);
                if (empty($rowMatches[0])) return $match[0];

                $allRows   = $rowMatches[0];
                $headerRow = $allRows[0]; // baris pertama = header, pertahankan
                $dataRows  = array_slice($allRows, 1); // baris data

                if (empty($dataRows)) return $match[0];

                // Ambil template baris data (baris pertama data sebagai acuan style)
                // Kita perlu 2 template: ganjil dan genap (untuk zebra striping)
                $templateRowOdd  = $dataRows[0];
                $templateRowEven = count($dataRows) > 1 ? $dataRows[1] : $dataRows[0];

                // Ekstrak style background dari baris template
                $bgOdd  = $this->extractRowBackground($templateRowOdd);
                $bgEven = $this->extractRowBackground($templateRowEven);

                // Ekstrak template <td> dari baris pertama data
                // (struktur kolom, style, dll — kecuali background dan isi variabel)
                $tdTemplates = $this->extractTdTemplates($templateRowOdd);

                // Ekstrak nomor baris (kolom No) jika ada
                $hasNoColumn = $this->detectNoColumn($templateRowOdd);

                // Generate baris baru sesuai $totalRows
                $newRows = [];
                for ($i = 1; $i <= $totalRows; $i++) {
                    $isOdd  = ($i % 2 === 1);
                    $bgColor = $isOdd ? $bgOdd : $bgEven;
                    $rowHtml = $this->buildDaftarRow($tdTemplates, $i, $bgColor, $hasNoColumn, $daftarVarPattern);
                    $newRows[] = $rowHtml;
                }

                // Rebuild tabel: header + baris baru
                $newTableContent = $headerRow . implode('', $newRows);

                // Pertahankan <colgroup> jika ada
                preg_match('/<colgroup[^>]*>.*?<\/colgroup>/is', $tableContent, $colgroupMatch);
                $colgroup = $colgroupMatch[0] ?? '';

                return '<table' . $tableAttrs . '>' . $colgroup . $newTableContent . '</table>';
            },
            $htmlTemplate
        );

        return $result ?? $htmlTemplate;
    }

    /**
     * Ekstrak warna background dari baris <tr>.
     */
    private function extractRowBackground(string $trHtml): string
    {
        // Ambil background dari TD pertama
        if (preg_match('/background(?:-color)?:\s*([^;>"\']+)/i', $trHtml, $m)) {
            return trim($m[1]);
        }
        return '#ffffff';
    }

    /**
     * Ekstrak template <td> dari baris data (tanpa isi, pertahankan style).
     * Return array of td style strings.
     */
    private function extractTdTemplates(string $trHtml): array
    {
        preg_match_all('/<td([^>]*)>(.*?)<\/td>/is', $trHtml, $m);
        $templates = [];
        foreach ($m[1] as $idx => $attrs) {
            $templates[] = [
                'attrs'   => $attrs,
                'content' => $m[2][$idx] ?? '',
            ];
        }
        return $templates;
    }

    /**
     * Deteksi apakah kolom pertama adalah kolom "No" (nomor urut).
     */
    private function detectNoColumn(string $trHtml): bool
    {
        preg_match('/<td[^>]*>(.*?)<\/td>/is', $trHtml, $m);
        $firstCell = strip_tags($m[1] ?? '');
        // Kolom No biasanya berisi angka kecil
        return is_numeric(trim($firstCell));
    }

    /**
     * Build satu baris <tr> untuk tabel daftar.
     *
     * @param  array  $tdTemplates   template kolom dari baris asli
     * @param  int    $rowNum        nomor baris (1-based)
     * @param  string $bgColor       warna background baris ini
     * @param  bool   $hasNoColumn   apakah kolom pertama adalah nomor urut
     * @param  string $varPattern    regex pattern variabel daftar
     */
    private function buildDaftarRow(
        array  $tdTemplates,
        int    $rowNum,
        string $bgColor,
        bool   $hasNoColumn,
        string $varPattern
    ): string {
        $tds = '';
        foreach ($tdTemplates as $colIdx => $td) {
            // Update background di style attr
            $attrs = preg_replace(
                '/background(?:-color)?:\s*[^;>"\']+/i',
                'background:' . $bgColor,
                $td['attrs']
            );

            $content = $td['content'];

            if ($hasNoColumn && $colIdx === 0) {
                // Kolom No → isi nomor urut
                $content = (string) $rowNum;
            } else {
                // Kolom data → ganti variabel dengan versi bernomor
                $content = preg_replace_callback(
                    '/\{\{(' . $varPattern . ')(?:_\d+)?\}\}/i',
                    fn($m) => '{{' . $m[1] . '_' . $rowNum . '}}',
                    $content
                );
            }

            $tds .= '<td' . $attrs . '>' . $content . '</td>';
        }

        return '<tr>' . $tds . '</tr>';
    }

    /**
     * Deteksi variabel daftar dari html_template yang tersimpan di DB.
     *
     * Logika: parse html_template, cari tabel yang punya variabel {{X}}
     * muncul di ≥ 2 baris data berbeda → variabel itu adalah variabel daftar.
     *
     * Ini cover template lama yang dibuat sebelum fitur table_mode ada,
     * di mana tabel daftar nama pakai {{nama_lengkap}} di tiap baris (bukan bernomor).
     *
     * Contoh html_template yang terdeteksi:
     *   <tr><td>1</td><td>{{nama_lengkap}}</td></tr>
     *   <tr><td>2</td><td>{{nama_lengkap}}</td></tr>  ← sama = daftar
     *   <tr><td>3</td><td>{{nama_lengkap}}</td></tr>
     *
     * @param  DocumentTemplate  $template
     * @param  array             $colToVar  mapping kolom Excel → varName
     * @return array<string>     base names variabel daftar, misal ['nama_lengkap']
     */
    private function detectListVarsFromHtml(DocumentTemplate $template, array $colToVar): array
    {
        $htmlTemplate = $template->html_template ?? '';
        if (empty($htmlTemplate)) return [];

        // Ambil semua tabel dari html_template
        preg_match_all('/<table[^>]*>(.*?)<\/table>/is', $htmlTemplate, $tableMatches);
        if (empty($tableMatches[1])) return [];

        $listVars = [];

        foreach ($tableMatches[1] as $tableContent) {
            // Ambil semua baris <tr>
            preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $tableContent, $rowMatches);
            if (empty($rowMatches[1])) continue;

            $dataRows = $rowMatches[1];
            // Skip baris pertama (header)
            if (count($dataRows) <= 1) continue;
            $dataRows = array_slice($dataRows, 1);

            // Hitung berapa kali tiap variabel muncul di baris-baris berbeda
            $varRowCount = []; // varName → jumlah baris yang memuat variabel ini

            foreach ($dataRows as $rowHtml) {
                preg_match_all('/\{\{([^}]+)\}\}/', $rowHtml, $varMatches);
                $varsInRow = array_unique(array_map('trim', $varMatches[1]));
                foreach ($varsInRow as $var) {
                    $varRowCount[$var] = ($varRowCount[$var] ?? 0) + 1;
                }
            }

            // Variabel yang muncul di ≥ 2 baris = variabel daftar
            foreach ($varRowCount as $var => $count) {
                if ($count >= 2 && !in_array($var, $listVars)) {
                    // Konfirmasi: variabel ini ada di Excel (ada kolomnya)
                    $excelVarBases = array_map(
                        fn($v) => preg_replace('/_\d+$/', '', $v),
                        array_values($colToVar)
                    );
                    $base = preg_replace('/_\d+$/', '', $var);
                    if (in_array($base, $excelVarBases) || in_array($var, array_values($colToVar))) {
                        $listVars[] = $var; // simpan nama asli (nama_lengkap, bukan bernomor)
                    }
                }
            }
        }

        return $listVars;
    }

    /**
     * Deteksi otomatis variabel daftar dari template canvas (backward-compatible).
     *
     * Logika: variabel yang di canvas pakai POLA BERNOMOR ({{nama_lengkap_1}},
     * {{nama_lengkap_2}}, ...) tapi di Excel hanya ada SATU kolom (nama_lengkap)
     * = kolom daftar yang harus dikumpulkan dari semua baris.
     *
     * Contoh:
     *   Template canvas punya: {{nama_lengkap_1}}, {{nama_lengkap_2}}, {{nama_lengkap_3}}
     *   Excel punya kolom: nama_lengkap (satu kolom)
     *   → deteksi: "nama_lengkap" adalah variabel daftar
     *
     * @param  DocumentTemplate  $template
     * @param  array             $colToVar  mapping kolom Excel → varName saat ini
     * @return array<string>     base names variabel daftar, misal ['nama_lengkap']
     */
    private function detectListVarsFromTemplate(DocumentTemplate $template, array $colToVar): array
    {
        $canvasJson = $template->canvas_json;
        if (empty($canvasJson)) return [];

        $data = is_array($canvasJson)
            ? $canvasJson
            : json_decode($canvasJson, true);

        if (!is_array($data)) return [];

        // Kumpulkan semua variabel di canvas (dari semua teks, semua halaman)
        $canvasVars = [];
        $this->collectCanvasVars($data, $canvasVars);

        // Cari variabel yang punya pola bernomor: nama_lengkap_1, nama_lengkap_2, dst
        // Group by base name
        $numberedBases = [];
        foreach ($canvasVars as $var) {
            if (preg_match('/^(.+)_(\d+)$/', $var, $m)) {
                $base = $m[1];
                $numberedBases[$base] = ($numberedBases[$base] ?? 0) + 1;
            }
        }

        // Hanya base name yang muncul ≥ 2 kali bernomor = variabel daftar
        $listBases = [];
        foreach ($numberedBases as $base => $count) {
            if ($count >= 2) {
                $listBases[] = $base;
            }
        }

        if (empty($listBases)) return [];

        // Konfirmasi: base name ini ada di Excel sebagai kolom TIDAK bernomor
        // (jika di Excel sudah bernomor nama_lengkap_1 bukan nama_lengkap, beda kasus)
        $excelVarBases = [];
        foreach ($colToVar as $col => $varName) {
            $excelVarBases[] = preg_replace('/_\d+$/', '', $varName);
        }

        $confirmed = [];
        foreach ($listBases as $base) {
            // Ada di canvas sebagai bernomor DAN ada di Excel sebagai kolom (bernomor atau tidak)
            if (in_array($base, $excelVarBases)) {
                $confirmed[] = $base;
            }
        }

        return $confirmed;
    }

    /**
     * Rekursif kumpulkan semua nama variabel {{...}} dari canvas JSON.
     */
    private function collectCanvasVars(array $data, array &$vars): void
    {
        // Cari di semua teks object canvas
        if (isset($data['objects']) && is_array($data['objects'])) {
            foreach ($data['objects'] as $obj) {
                $text = $obj['text'] ?? '';
                if ($text && preg_match_all('/\{\{([^}]+)\}\}/', $text, $m)) {
                    foreach ($m[1] as $v) {
                        $v = trim($v);
                        if ($v && !in_array($v, $vars)) $vars[] = $v;
                    }
                }
                // Rekursif untuk group
                if (!empty($obj['objects'])) {
                    $this->collectCanvasVars($obj, $vars);
                }
            }
        }

        // Cari di tableStore
        if (isset($data['_tableStore']) && is_array($data['_tableStore'])) {
            foreach ($data['_tableStore'] as $tableId => $td) {
                foreach ($td['rows'] ?? [] as $row) {
                    foreach ($row as $cell) {
                        $text = $cell['text'] ?? '';
                        if ($text && preg_match_all('/\{\{([^}]+)\}\}/', $text, $m)) {
                            foreach ($m[1] as $v) {
                                $v = trim($v);
                                if ($v && !in_array($v, $vars)) $vars[] = $v;
                            }
                        }
                    }
                }
            }
        }

        // Multi-halaman v2
        if (isset($data['pages']) && is_array($data['pages'])) {
            foreach ($data['pages'] as $page) {
                $this->collectCanvasVars($page, $vars);
            }
        }
    }

    /**
     * Ekstrak peta table_id → table_mode dari canvas_json template.
     *
     * Format canvas_json v2 (multi-halaman):
     *   { version: 2, pages: [ { _tableStore: { tbl_1: { table_mode: 'daftar', ... } } } ] }
     *
     * Format canvas_json v1 (single page):
     *   { _tableStore: { tbl_1: { table_mode: 'perorang', ... } } }
     *
     * Tabel tanpa field table_mode di-default ke 'perorang' (backward compatible).
     *
     * @param  DocumentTemplate  $template
     * @return array<string, string>  ['tbl_1' => 'perorang', 'tbl_2' => 'daftar', ...]
     */
    private function extractTableModeMap(DocumentTemplate $template): array
    {
        $canvasJson = $template->canvas_json;
        if (empty($canvasJson)) return [];

        $data = is_array($canvasJson)
            ? $canvasJson
            : json_decode($canvasJson, true);

        if (!is_array($data)) return [];

        $modeMap = [];

        // Format v2: multi-halaman
        if (isset($data['version']) && $data['version'] === 2 && !empty($data['pages'])) {
            foreach ($data['pages'] as $page) {
                $tableStore = $page['_tableStore'] ?? [];
                foreach ($tableStore as $tableId => $tableData) {
                    $modeMap[$tableId] = $tableData['table_mode'] ?? 'perorang';
                }
            }
            return $modeMap;
        }

        // Format v1: single page
        $tableStore = $data['_tableStore'] ?? [];
        foreach ($tableStore as $tableId => $tableData) {
            $modeMap[$tableId] = $tableData['table_mode'] ?? 'perorang';
        }

        return $modeMap;
    }

    /**
     * Ekstrak daftar nama variabel yang berasal dari tabel mode 'daftar'.
     *
     * Membaca canvas_json → tableStore, cari tabel dengan table_mode = 'daftar',
     * lalu kumpulkan semua variabel {{...}} yang ada di sel tabel tersebut.
     *
     * Hasilnya dipakai oleh batchGenerate() untuk memisahkan:
     *  - variabel "daftar" → dikumpulkan dari semua baris Excel jadi bernomor (nama_1, nama_2, ...)
     *  - variabel "perorang" → diambil dari baris Excel masing-masing siswa
     *
     * @param  DocumentTemplate  $template
     * @return array<string>  ['nama_lengkap', 'kelas', ...]  — base name tanpa nomor
     */
    private function extractDaftarVarNames(DocumentTemplate $template): array
    {
        $canvasJson = $template->canvas_json;
        if (empty($canvasJson)) return [];

        $data = is_array($canvasJson)
            ? $canvasJson
            : json_decode($canvasJson, true);

        if (!is_array($data)) return [];

        $daftarVars = [];

        // Kumpulkan semua tableStore dari semua halaman (v1 & v2)
        $allTableStores = [];
        if (isset($data['version']) && $data['version'] === 2 && !empty($data['pages'])) {
            foreach ($data['pages'] as $page) {
                if (!empty($page['_tableStore'])) {
                    $allTableStores[] = $page['_tableStore'];
                }
            }
        } else {
            if (!empty($data['_tableStore'])) {
                $allTableStores[] = $data['_tableStore'];
            }
        }

        // Cari tabel dengan table_mode = 'daftar' dan ekstrak variabel dari sel-selnya
        foreach ($allTableStores as $tableStore) {
            foreach ($tableStore as $tableId => $tableData) {
                $mode = $tableData['table_mode'] ?? 'perorang';
                if ($mode !== 'daftar') continue;

                // Scan semua sel tabel untuk variabel {{...}}
                $rows = $tableData['rows'] ?? [];
                foreach ($rows as $row) {
                    foreach ($row as $cell) {
                        $text = $cell['text'] ?? '';
                        if (preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches)) {
                            foreach ($matches[1] as $varName) {
                                $varName = trim($varName);
                                // Simpan base name — hapus nomor akhir jika ada (nama_1 → nama)
                                $baseName = preg_replace('/_\d+$/', '', $varName);
                                if ($baseName && !in_array($baseName, $daftarVars)) {
                                    $daftarVars[] = $baseName;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $daftarVars;
    }

    /**
     * Bangun mapping kolom → base variable name (tanpa nomor) untuk mode daftar.
     * Contoh: kolom A header 'Nama', hidden '__VAR__nama_1' → ['A' => 'nama']
     */
    private function buildBaseVarMap(array $headerRow, array $hiddenRow): array
    {
        $baseMap = [];
        foreach ($headerRow as $col => $label) {
            if (empty($label)) continue;

            $hiddenVal = trim((string) ($hiddenRow[$col] ?? ''));
            if (str_starts_with($hiddenVal, '__VAR__')) {
                $varName       = substr($hiddenVal, 7);
                $base          = preg_replace('/_\d+$/', '', $varName);
                $baseMap[$col] = $base;
                continue;
            }

            $slug = strtolower(trim((string) $label));
            $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
            $slug = trim($slug, '_');
            if ($slug) {
                $baseMap[$col] = $slug;
            }
        }
        return $baseMap;
    }

    /**
     * Membangun peta kolom Excel → nama variabel template.
     *
     * Urutan prioritas:
     *  1. Hidden row baris 4 (berisi "__VAR__nama_variabel") — paling akurat
     *  2. Exact slug match antara label header dan nama variabel
     *  3. Exact match nama variabel langsung
     *  4. Normalized partial match
     */
    private function buildColToVarMap(array $headerRow, array $hiddenRow, array $variables): array
    {
        $colToVar = [];

        foreach ($headerRow as $col => $label) {
            if (empty($label)) continue;

            // Prioritas 1: Hidden variable row
            $hiddenVal = trim((string) ($hiddenRow[$col] ?? ''));
            if (str_starts_with($hiddenVal, '__VAR__')) {
                $varName = substr($hiddenVal, 7);
                if (in_array($varName, $variables)) {
                    $colToVar[$col] = $varName;
                    continue;
                }
            }

            $labelClean = strtolower(trim((string) $label));

            // Prioritas 2: Exact slug match
            $slug = strtolower(str_replace([' ', '-'], '_', $labelClean));
            if (in_array($slug, $variables)) {
                $colToVar[$col] = $slug;
                continue;
            }

            // Prioritas 3: Match langsung nama variabel
            if (in_array($labelClean, $variables)) {
                $colToVar[$col] = $labelClean;
                continue;
            }

            // Prioritas 4: Normalized partial match
            $labelNorm = preg_replace('/[^a-z0-9]/', '', $labelClean);

            if (!empty($labelNorm)) {
                $bestMatch = null;
                $bestScore = 0;

                foreach ($variables as $varName) {
                    $varNorm = preg_replace('/[^a-z0-9]/', '', strtolower($varName));
                    $minLen  = min(strlen($labelNorm), strlen($varNorm));

                    if ($minLen < 4) continue;

                    $labelPrefix = substr($labelNorm, 0, $minLen);
                    $varPrefix   = substr($varNorm, 0, $minLen);

                    if ($labelPrefix === $varPrefix) {
                        $score = $minLen;
                        if ($score > $bestScore && !in_array($varName, $colToVar)) {
                            $bestScore = $score;
                            $bestMatch = $varName;
                        }
                    }
                }

                if ($bestMatch !== null) {
                    $colToVar[$col] = $bestMatch;
                }
            }
        }

        return $colToVar;
    }

    /**
     * Membersihkan nilai cell dari Excel menjadi string yang aman.
     */
    private function sanitizeCellValue(mixed $value): string
    {
        if ($value === null) return '';

        if (is_bool($value)) return $value ? '1' : '0';

        if (is_float($value) || is_int($value)) {
            if (is_float($value) && floor($value) == $value && abs($value) < 1e15) {
                return number_format($value, 0, '.', '');
            }
            return (string) $value;
        }

        return trim((string) $value);
    }

    /**
     * Mengubah nama variabel snake_case menjadi label cantik untuk header Excel.
     */
    private function friendlyVarLabel(string $varName): string
    {
        $map = [
            'nama_siswa'     => 'Nama Siswa',
            'nisn'           => 'NISN',
            'nis'            => 'NIS',
            'jenis_kelamin'  => 'Jenis Kelamin',
            'tempat_lahir'   => 'Tempat Lahir',
            'tanggal_lahir'  => 'Tanggal Lahir',
            'agama'          => 'Agama',
            'nama_ayah'      => 'Nama Ayah',
            'nama_ibu'       => 'Nama Ibu',
            'pekerjaan_ayah' => 'Pekerjaan Ayah',
            'pekerjaan_ibu'  => 'Pekerjaan Ibu',
            'alamat_siswa'   => 'Alamat',
            'no_hp'          => 'No. HP',
            'nama_wali'      => 'Nama Wali',
            'nama_sekolah'   => 'Nama Sekolah',
            'alamat_sekolah' => 'Alamat Sekolah',
            'kepala_sekolah' => 'Kepala Sekolah',
            'nip'            => 'NIP/NBM',
            'tahun_ajaran'   => 'Tahun Ajaran',
            'semester'       => 'Semester',
            'wali_kelas'     => 'Wali Kelas',
            'nbm_wali'       => 'NBM Wali Kelas',
            'nomor_surat'    => 'Nomor Surat',
            'tanggal'        => 'Tanggal',
            'perihal'        => 'Perihal',
            'keterangan'     => 'Keterangan',
            'isi'            => 'Isi Surat',
            'tujuan'         => 'Tujuan',
            'tembusan'       => 'Tembusan',
            'kelas'          => 'Kelas',
            'fase'           => 'Fase',
            'nama_kelas'     => 'Nama Kelas',
            'nilai_rata'     => 'Nilai Rata-rata',
            'peringkat'      => 'Peringkat',
            'predikat'       => 'Predikat',
            'catatan'        => 'Catatan',
            'naik_kelas'     => 'Naik Kelas',
            'mata_pelajaran' => 'Mata Pelajaran',
            'nama_ortu'      => 'Nama Orang Tua',
        ];

        if (isset($map[$varName])) return $map[$varName];

        if (preg_match('/^nilai_(.+)$/', $varName, $m)) {
            return 'Nilai ' . ucwords(str_replace('_', ' ', $m[1]));
        }
        if (preg_match('/^capaian_(.+)$/', $varName, $m)) {
            return 'Capaian ' . ucwords(str_replace('_', ' ', $m[1]));
        }

        return ucwords(str_replace('_', ' ', $varName));
    }

    /**
     * Menghasilkan contoh nilai untuk setiap variabel di baris contoh Excel.
     */
    private function getExampleValue(string $varKey): string
    {
        $examples = [
            'nama_siswa'     => 'Ahmad Fauzi',
            'nisn'           => '0012345678',
            'nis'            => '2324001',
            'jenis_kelamin'  => 'Laki-laki',
            'tempat_lahir'   => 'Samarinda',
            'tanggal_lahir'  => '10 Mei 2015',
            'agama'          => 'Islam',
            'nama_ayah'      => 'Budi Santoso',
            'nama_ibu'       => 'Siti Aminah',
            'pekerjaan_ayah' => 'Wiraswasta',
            'pekerjaan_ibu'  => 'Ibu Rumah Tangga',
            'alamat_siswa'   => 'Jl. Sungai Keledang No. 10',
            'no_hp'          => '081234567890',
            'nama_wali'      => 'Budi Santoso',
            'nama_sekolah'   => 'SD Muhammadiyah 3 Samarinda',
            'alamat_sekolah' => 'Jl. Dato Iba, Samarinda Seberang',
            'kepala_sekolah' => 'Drs. H. Mahmud, M.Pd',
            'nip'            => '197001012000031001',
            'tahun_ajaran'   => '2024/2025',
            'semester'       => 'Genap',
            'wali_kelas'     => 'Ibu Rahmawati, S.Pd',
            'nbm_wali'       => '1234567',
            'nomor_surat'    => '001/SKA/IV/2025',
            'tanggal'        => now()->translatedFormat('d F Y'),
            'perihal'        => 'Keterangan Aktif Belajar',
            'keterangan'     => 'Yang bersangkutan adalah siswa aktif',
            'isi'            => 'Isi surat keterangan...',
            'tujuan'         => 'Kepada Yth. ...',
            'tembusan'       => '1. Arsip',
            'kelas'          => '4A',
            'fase'           => 'Fase B',
            'nama_kelas'     => 'Kelas 4A',
            'nilai_rata'     => '88.50',
            'peringkat'      => '3',
            'predikat'       => 'Baik',
            'catatan'        => 'Siswa menunjukkan perkembangan yang baik',
            'naik_kelas'     => 'NAIK KELAS',
            'mata_pelajaran' => 'Matematika',
            'nama_ortu'      => 'Budi Santoso',
        ];

        if (isset($examples[$varKey])) return $examples[$varKey];

        if (preg_match('/^nilai_(.+)$/', $varKey)) return '85';
        if (preg_match('/^capaian_(.+)$/', $varKey)) return 'Peserta didik mampu memahami materi dengan baik';

        return 'Contoh ' . ucwords(str_replace('_', ' ', $varKey));
    }
}