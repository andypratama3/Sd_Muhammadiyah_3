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
        $colorHeader      = 'FF1A5276';   // biru tua — header utama
        $colorHeaderRaport= 'FF154360';   // biru lebih gelap — kolom prefix raport
        $colorSubHeader   = 'FF2980B9';   // biru medium — sub-header info
        $colorRowOdd      = 'FFF0F7FF';   // biru sangat muda — baris ganjil
        $colorRowEven     = 'FFFFFFFF';   // putih — baris genap
        $colorAccent      = 'FF27AE60';   // hijau — highlight penting
        $colorHidden      = 'FFFFFFFF';   // putih — baris hidden var
        $colorExample     = 'FFFFFDE7';   // kuning sangat muda — baris contoh

        // ── BARIS 1: JUDUL TEMPLATE ───────────────────────────────────────────
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->setCellValue('A1', '📋  Template: ' . $template->name
            . '   |   Kategori: ' . ($template->category?->name ?? '-')
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
        $sheet->setCellValue('A2',
            '⚠  Isi data mulai baris ke-5. Jangan ubah/hapus baris 3 dan 4. Setiap baris = 1 dokumen PDF.');

        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold'   => true,
                'size'   => 9,
                'name'   => 'Arial',
                'color'  => ['argb' => 'FF7D3C00'],
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

        // ── BARIS 3: HEADER LABEL (yang user baca) ────────────────────────────
        $colIndex = 1;
        foreach ($allColumns as $varKey => $label) {
            $cell      = Coordinate::stringFromColumnIndex($colIndex) . '3';
            $isPrefix  = $isRaport && isset($prefixColumns[$varKey]);
            $bgColor   = $isPrefix ? $colorHeaderRaport : $colorHeader;

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

            // Lebar kolom otomatis (min 16, max 38)
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
                    'color' => ['argb' => $colorHidden], // invisible — sama dengan bg
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $colorHidden],
                ],
            ]);
            $colIndex++;
        }
        $sheet->getRowDimension(4)->setRowHeight(3); // sangat tipis

        // ── BARIS 5: CONTOH DATA (italic, warna muda) ─────────────────────────
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

        // ── BARIS 6+: Area data kosong (zebra striping 20 baris) ─────────────
        for ($row = 6; $row <= 25; $row++) {
            $bgColor = ($row % 2 === 0) ? $colorRowEven : $colorRowOdd;
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

        // ── BORDER TEBAL DI SEKELILING HEADER ────────────────────────────────
        $sheet->getStyle('A3:' . $lastCol . '3')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['argb' => 'FF1A5276'],
                ],
            ],
        ]);

        // ── BORDER TEBAL DI SEKELILING AREA DATA ─────────────────────────────
        $sheet->getStyle('A5:' . $lastCol . '25')->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['argb' => 'FF2980B9'],
                ],
            ],
        ]);

        // ── FREEZE PANE di baris 5 (setelah header + hidden row) ─────────────
        $sheet->freezePane('A5');

        // ── KOLOM A: lebar minimal lebih lebar jika ada prefix ────────────────
        if ($isRaport) {
            $sheet->getColumnDimension('A')->setWidth(18); // NISN
            $sheet->getColumnDimension('B')->setWidth(28); // Nama Siswa
        }

        // =========================================================================
        // SHEET 2: PETUNJUK PENGISIAN
        // =========================================================================

        $info = $spreadsheet->createSheet();
        $info->setTitle('Petunjuk');

        // Judul
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

        // Info template
        $infoMeta = [
            ['Template',  $template->name],
            ['Kategori',  $template->category?->name ?? '-'],
            ['Variabel',  count($allColumns) . ' kolom'],
            ['Dibuat',    now()->format('d M Y H:i')],
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

        // Panduan langkah
        $steps = [
            ['no' => '1', 'step' => 'Buka sheet "Data"'],
            ['no' => '2', 'step' => 'Isi data mulai dari BARIS KE-5 (baris kuning muda adalah contoh, boleh ditimpa)'],
            ['no' => '3', 'step' => 'Setiap baris = satu dokumen PDF yang akan digenerate'],
            ['no' => '4', 'step' => 'JANGAN mengubah atau menghapus baris 3 (header biru)'],
            ['no' => '5', 'step' => 'JANGAN mengubah atau menghapus baris 4 (baris sangat tipis/tidak terlihat — kode sistem)'],
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

        // Catatan khusus raport
        if ($isRaport) {
            $row++;
            $info->setCellValue('A' . $row, '⚠ CATATAN KHUSUS RAPORT:');
            $info->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FFC0392B']],
            ]);
            $row++;

            $raportNotes = [
                'Kolom NISN dan Nama Siswa WAJIB diisi untuk setiap baris.',
                'NISN harus sesuai dengan data siswa di sistem.',
                'Kolom nilai diisi sesuai nama variabel masing-masing mata pelajaran.',
                'Pastikan format nilai sesuai (angka/huruf tergantung kurikulum).',
            ];
            foreach ($raportNotes as $note) {
                $info->setCellValue('B' . $row, '• ' . $note);
                $info->getStyle('B' . $row)->applyFromArray([
                    'font'      => ['size' => 10, 'name' => 'Arial', 'color' => ['argb' => 'FFC0392B'], 'bold' => true],
                    'alignment' => ['wrapText' => true],
                ]);
                $info->getRowDimension($row)->setRowHeight(18);
                $row++;
            }
        }

        // Daftar variabel yang tersedia
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
            'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial', 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $colorSubHeader]],
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

        // ── Aktifkan sheet Data sebagai sheet aktif ───────────────────────────
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

        // Baris 1 = judul template (skip)
        // Baris 2 = panduan (skip)
        // Baris 3 = header label
        // Baris 4 = hidden var row
        // Baris 5+ = data

        array_shift($rows); // skip baris 1 (judul)
        array_shift($rows); // skip baris 2 (panduan)
        $headerRow = array_shift($rows); // baris 3 — label
        $hiddenRow = !empty($rows) ? array_shift($rows) : []; // baris 4 — __VAR__

        $colToVar      = $this->buildColToVarMap($headerRow, $hiddenRow, $variables);
        $usePositional = empty($colToVar);
        $colKeys       = array_keys($headerRow);

        $result = [];
        $index  = 1;

        foreach ($rows as $row) {
            $values = array_filter(array_values($row), fn($v) => $v !== null && $v !== '');
            if (empty($values)) {
                continue;
            }

            $firstVal = trim((string) (array_values($row)[0] ?? ''));
            if (str_starts_with($firstVal, 'Contoh ')) {
                continue;
            }

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

        return response()->json([
            'rows'            => $result,
            'total'           => count($result),
            'mapping_method'  => $usePositional ? 'positional' : 'mapped',
            'variables_found' => array_values($colToVar),
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

        // Skip baris 1 (judul), 2 (panduan), ambil 3 (header), 4 (hidden)
        array_shift($rows); // baris 1
        array_shift($rows); // baris 2
        $headerRow     = array_shift($rows); // baris 3
        $hiddenRow     = !empty($rows) ? array_shift($rows) : []; // baris 4
        $colToVar      = $this->buildColToVarMap($headerRow, $hiddenRow, $allKnownVariables);
        $usePositional = empty($colToVar);
        $colKeys       = array_keys($headerRow);

        $zipPath = sys_get_temp_dir() . '/batch_' . uniqid() . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $count = 0;
        foreach ($rows as $rowNum => $row) {
            $values = array_filter(array_values($row), fn($v) => $v !== null && $v !== '');
            if (empty($values)) {
                continue;
            }

            $firstVal = trim((string) (array_values($row)[0] ?? ''));
            if (str_starts_with($firstVal, 'Contoh ')) {
                continue;
            }

            $userData = [];

            if ($usePositional) {
                foreach ($allKnownVariables as $varIndex => $varName) {
                    $colKey             = $colKeys[$varIndex] ?? null;
                    $userData[$varName] = $colKey && isset($row[$colKey])
                        ? $this->sanitizeCellValue($row[$colKey])
                        : '';
                }
            } else {
                foreach ($colToVar as $col => $varName) {
                    $userData[$varName] = isset($row[$col])
                        ? $this->sanitizeCellValue($row[$col])
                        : '';
                }
                foreach ($allKnownVariables as $varName) {
                    if (!array_key_exists($varName, $userData)) {
                        $userData[$varName] = '';
                    }
                }
            }

            try {
                $document   = $this->generatorService->generate($template, $userData, []);
                $pdfContent = \Storage::disk('public')->get($document->file_path);

                if ($isRaport && !empty($userData['nama_siswa'])) {
                    $nameSlug    = Str::slug($userData['nama_siswa']);
                    $nisnSuffix  = !empty($userData['nisn']) ? '-' . $userData['nisn'] : '';
                    $zipFileName = sprintf('%03d-%s%s.pdf', $count + 1, $nameSlug, $nisnSuffix);
                } else {
                    $firstValue  = Str::slug(array_values($userData)[0] ?? 'dokumen');
                    $zipFileName = sprintf('%03d-%s.pdf', $count + 1, $firstValue ?: 'dokumen');
                }

                $zip->addFromString($zipFileName, $pdfContent);
                $count++;
            } catch (\Throwable $e) {
                \Log::warning("Batch generate row {$rowNum} failed: " . $e->getMessage());
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
            if (count($line) !== count($headers)) {
                continue;
            }
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
     * Membangun peta kolom Excel → nama variabel template.
     *
     * Urutan prioritas:
     *  1. Hidden row baris 4 (berisi "__VAR__nama_variabel") — paling akurat
     *  2. Exact slug match antara label header dan nama variabel
     *  3. Normalized partial match (strip non-alphanumeric, prefix match)
     */
    private function buildColToVarMap(array $headerRow, array $hiddenRow, array $variables): array
    {
        $colToVar = [];

        foreach ($headerRow as $col => $label) {
            if (empty($label)) {
                continue;
            }

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

            // Prioritas 3: Normalized partial match
            $labelNorm = preg_replace('/[^a-z0-9]/', '', $labelClean);

            if (!empty($labelNorm)) {
                $bestMatch = null;
                $bestScore = 0;

                foreach ($variables as $varName) {
                    $varNorm = preg_replace('/[^a-z0-9]/', '', strtolower($varName));
                    $minLen  = min(strlen($labelNorm), strlen($varNorm));

                    if ($minLen < 4) {
                        continue;
                    }

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
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

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
     * Mapping statis sesuai TemplateVariableRegistry — konsisten dengan JS editor.
     */
    private function friendlyVarLabel(string $varName): string
    {
        $map = [
            // Data Siswa
            'nama_siswa'      => 'Nama Siswa',
            'nisn'            => 'NISN',
            'nis'             => 'NIS',
            'jenis_kelamin'   => 'Jenis Kelamin',
            'tempat_lahir'    => 'Tempat Lahir',
            'tanggal_lahir'   => 'Tanggal Lahir',
            'agama'           => 'Agama',
            'nama_ayah'       => 'Nama Ayah',
            'nama_ibu'        => 'Nama Ibu',
            'pekerjaan_ayah'  => 'Pekerjaan Ayah',
            'pekerjaan_ibu'   => 'Pekerjaan Ibu',
            'alamat_siswa'    => 'Alamat',
            'no_hp'           => 'No. HP',
            'nama_wali'       => 'Nama Wali',
            // Data Sekolah
            'nama_sekolah'    => 'Nama Sekolah',
            'alamat_sekolah'  => 'Alamat Sekolah',
            'kepala_sekolah'  => 'Kepala Sekolah',
            'nip'             => 'NIP/NBM',
            'tahun_ajaran'    => 'Tahun Ajaran',
            'semester'        => 'Semester',
            'wali_kelas'      => 'Wali Kelas',
            'nbm_wali'        => 'NBM Wali Kelas',
            // Data Surat
            'nomor_surat'     => 'Nomor Surat',
            'tanggal'         => 'Tanggal',
            'perihal'         => 'Perihal',
            'keterangan'      => 'Keterangan',
            'isi'             => 'Isi Surat',
            'tujuan'          => 'Tujuan',
            'tembusan'        => 'Tembusan',
            // Nilai & Prestasi
            'kelas'           => 'Kelas',
            'fase'            => 'Fase',
            'nama_kelas'      => 'Nama Kelas',
            'nilai_rata'      => 'Nilai Rata-rata',
            'peringkat'       => 'Peringkat',
            'predikat'        => 'Predikat',
            'catatan'         => 'Catatan',
            'naik_kelas'      => 'Naik Kelas',
            'mata_pelajaran'  => 'Mata Pelajaran',
            // Lainnya
            'nama_ortu'       => 'Nama Orang Tua',
        ];

        if (isset($map[$varName])) {
            return $map[$varName];
        }

        // Pola auto-generate raport: nilai_xxx / capaian_xxx
        if (preg_match('/^nilai_(.+)$/', $varName, $m)) {
            return 'Nilai ' . ucwords(str_replace('_', ' ', $m[1]));
        }
        if (preg_match('/^capaian_(.+)$/', $varName, $m)) {
            return 'Capaian ' . ucwords(str_replace('_', ' ', $m[1]));
        }

        // Fallback: snake_case → Title Case
        return ucwords(str_replace('_', ' ', $varName));
    }

    /**
     * Menghasilkan contoh nilai untuk setiap variabel di baris contoh Excel.
     */
    private function getExampleValue(string $varKey): string
    {
        $examples = [
            'nama_siswa'      => 'Ahmad Fauzi',
            'nisn'            => '0012345678',
            'nis'             => '2324001',
            'jenis_kelamin'   => 'Laki-laki',
            'tempat_lahir'    => 'Samarinda',
            'tanggal_lahir'   => '10 Mei 2015',
            'agama'           => 'Islam',
            'nama_ayah'       => 'Budi Santoso',
            'nama_ibu'        => 'Siti Aminah',
            'pekerjaan_ayah'  => 'Wiraswasta',
            'pekerjaan_ibu'   => 'Ibu Rumah Tangga',
            'alamat_siswa'    => 'Jl. Sungai Keledang No. 10',
            'no_hp'           => '081234567890',
            'nama_wali'       => 'Budi Santoso',
            'nama_sekolah'    => 'SD Muhammadiyah 3 Samarinda',
            'alamat_sekolah'  => 'Jl. Dato Iba, Samarinda Seberang',
            'kepala_sekolah'  => 'Drs. H. Mahmud, M.Pd',
            'nip'             => '197001012000031001',
            'tahun_ajaran'    => '2024/2025',
            'semester'        => 'Genap',
            'wali_kelas'      => 'Ibu Rahmawati, S.Pd',
            'nbm_wali'        => '1234567',
            'nomor_surat'     => '001/SKA/IV/2025',
            'tanggal'         => now()->translatedFormat('d F Y'),
            'perihal'         => 'Keterangan Aktif Belajar',
            'keterangan'      => 'Yang bersangkutan adalah siswa aktif',
            'isi'             => 'Isi surat keterangan...',
            'tujuan'          => 'Kepada Yth. ...',
            'tembusan'        => '1. Arsip',
            'kelas'           => '4A',
            'fase'            => 'Fase B',
            'nama_kelas'      => 'Kelas 4A',
            'nilai_rata'      => '88.50',
            'peringkat'       => '3',
            'predikat'        => 'Baik',
            'catatan'         => 'Siswa menunjukkan perkembangan yang baik',
            'naik_kelas'      => 'NAIK KELAS',
            'mata_pelajaran'  => 'Matematika',
            'nama_ortu'       => 'Budi Santoso',
        ];

        if (isset($examples[$varKey])) {
            return $examples[$varKey];
        }

        // Pola nilai/capaian raport
        if (preg_match('/^nilai_(.+)$/', $varKey)) {
            return '85';
        }
        if (preg_match('/^capaian_(.+)$/', $varKey)) {
            return 'Peserta didik mampu memahami materi dengan baik';
        }

        return 'Contoh ' . ucwords(str_replace('_', ' ', $varKey));
    }
}