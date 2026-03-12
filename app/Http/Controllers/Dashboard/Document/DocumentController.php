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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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
        $documents = Document::with('template.category', 'student')
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
    //
    // PENTING: Baris pertama menyimpan nama variabel ASLI (snake_case) sebagai
    // nilai tersembunyi di baris ke-2 untuk keperluan parsing balik.
    //
    // Struktur sheet "Data":
    //   Baris 1  = Header LABEL cantik (untuk dibaca manusia)
    //   Baris 2  = Header VARIABLE asli (disembunyikan dengan warna putih
    //              di background putih, digunakan saat parseExcel)
    //   Baris 3+ = Data isian pengguna
    //
    // Dengan cara ini, parseExcel() selalu bisa menemukan nama variabel yang
    // tepat tanpa tebak-tebakan fuzzy matching.
    // =========================================================================

    public function excelTemplate(DocumentTemplate $template): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $template->load('category');
        $variables = $template->extractVariables();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        // ── Baris 1: Label cantik (untuk pengguna) ────────────────────────
        foreach ($variables as $colIndex => $var) {
            $col   = $colIndex + 1;
            $cell  = Coordinate::stringFromColumnIndex($col) . '1';
            $label = $this->friendlyVarLabel($var);

            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                    'name'  => 'Arial',
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A5276'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFB0BEC5'],
                    ],
                ],
            ]);

            $sheet->getColumnDimensionByColumn($col)->setWidth(
                max(18, min(40, strlen($label) + 4))
            );
        }

        $sheet->getRowDimension(1)->setRowHeight(24);

        // ── Baris 2: Nama variabel ASLI (hidden row, teks putih di bg putih)
        //    Ini kunci untuk parseExcel() agar matching selalu akurat.
        foreach ($variables as $colIndex => $var) {
            $col  = $colIndex + 1;
            $cell = Coordinate::stringFromColumnIndex($col) . '2';
            $sheet->setCellValue($cell, '__VAR__' . $var);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'color' => ['argb' => 'FFFFFFFF'], // teks putih = tidak terlihat
                    'size'  => 8,
                    'name'  => 'Arial',
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFFFF'], // bg putih
                ],
            ]);
        }
        $sheet->getRowDimension(2)->setRowHeight(4); // baris sangat tipis

        // ── Baris 3: Contoh data ──────────────────────────────────────────
        foreach ($variables as $colIndex => $var) {
            $col  = $colIndex + 1;
            $cell = Coordinate::stringFromColumnIndex($col) . '3';
            $sheet->setCellValue($cell, 'Contoh ' . ucwords(str_replace('_', ' ', $var)));
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['italic' => true, 'color' => ['argb' => 'FF9E9E9E'], 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F5F5']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }
        $sheet->getRowDimension(3)->setRowHeight(20);

        $sheet->freezePane('A4');

        // ── Sheet Petunjuk ────────────────────────────────────────────────
        $info = $spreadsheet->createSheet();
        $info->setTitle('Petunjuk');
        $info->setCellValue('A1', 'PETUNJUK PENGISIAN');
        $info->setCellValue('A3', '1. Isi data mulai dari BARIS KE-4 pada sheet "Data" (baris 1 = header, baris 2 = kode sistem, baris 3 = contoh yang bisa dihapus).');
        $info->setCellValue('A4', '2. Setiap baris = satu dokumen PDF yang akan digenerate.');
        $info->setCellValue('A5', '3. JANGAN mengubah nama kolom pada baris header (baris 1).');
        $info->setCellValue('A6', '4. JANGAN menghapus baris 2 (baris tipis berisi kode sistem).');
        $info->setCellValue('A7', '5. Upload file ini kembali di halaman Generate untuk memproses semua data sekaligus.');
        $info->setCellValue('A9',  'Template : ' . $template->name);
        $info->setCellValue('A10', 'Kategori : ' . $template->category->name);
        $info->setCellValue('A11', 'Dibuat   : ' . now()->format('d M Y H:i'));
        $info->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $info->getStyle('A9:A11')->getFont()->setItalic(true)->getColor()->setARGB('FF757575');
        $info->getColumnDimension('A')->setWidth(100);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'template-' . Str::slug($template->name) . '.xlsx';

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
    // PARSE EXCEL  (dipanggil oleh JS batch generate)
    // =========================================================================
    //
    // Strategi matching header → variabel (urutan prioritas):
    //
    //  1. Hidden variable row  — baris ke-2 berisi "__VAR__nama_variabel"
    //     Ini cara paling akurat. Template baru selalu punya baris ini.
    //
    //  2. Exact slug match  — header di-slugify lalu dicocokkan langsung
    //     ke daftar variabel template. Cocok jika header tidak diubah.
    //
    //  3. Normalized match  — strip karakter non-alphanumeric, bandingkan
    //     versi bersih header dengan versi bersih nama variabel.
    //     Contoh: "Nilai Agam" vs "nilai_agama_islamttze" → keduanya
    //     menjadi "nilaiagam" → partial match diterima.
    //
    //  4. Positional fallback  — jika semua cara gagal, urutan kolom di
    //     Excel dipetakan ke urutan variabel template secara berurutan.
    //     Ini menangani kasus template lama yang headernya sudah dipotong.
    //
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

        // ── Ambil baris 1 (header label) dan baris 2 (hidden var row) ────
        $headerRow  = array_shift($rows); // baris 1
        $hiddenRow  = !empty($rows) ? array_shift($rows) : []; // baris 2

        // ── Bangun peta kolom → variabel ──────────────────────────────────
        $colToVar = $this->buildColToVarMap($headerRow, $hiddenRow, $variables);

        // ── Jika mapping kosong, gunakan positional fallback ──────────────
        $usePositional = empty($colToVar);
        $colKeys       = array_keys($headerRow);

        // ── Proses baris data ─────────────────────────────────────────────
        $result = [];
        $index  = 1;

        foreach ($rows as $row) {
            // Skip baris kosong
            $values = array_filter(array_values($row), fn($v) => $v !== null && $v !== '');
            if (empty($values)) {
                continue;
            }

            // Skip baris contoh (dimulai dengan "Contoh ")
            $firstVal = trim((string) (array_values($row)[0] ?? ''));
            if (str_starts_with($firstVal, 'Contoh ')) {
                continue;
            }

            $data = [];

            if ($usePositional) {
                // Positional fallback: urutan kolom = urutan variabel
                foreach ($variables as $varIndex => $varName) {
                    $colKey      = $colKeys[$varIndex] ?? null;
                    $data[$varName] = $colKey && isset($row[$colKey])
                        ? $this->sanitizeCellValue($row[$colKey])
                        : '';
                }
            } else {
                // Mapping normal
                foreach ($colToVar as $col => $varName) {
                    $data[$varName] = isset($row[$col])
                        ? $this->sanitizeCellValue($row[$col])
                        : '';
                }
                // Pastikan semua variabel ada di data (isi string kosong jika tidak ada di Excel)
                foreach ($variables as $varName) {
                    if (!array_key_exists($varName, $data)) {
                        $data[$varName] = '';
                    }
                }
            }

            // Label untuk ditampilkan di UI
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
    // BATCH GENERATE (ZIP download, dipanggil langsung tanpa JS)
    // =========================================================================

    public function batchGenerate(Request $request, DocumentTemplate $template): Response
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

        $headerRow = array_shift($rows);
        $hiddenRow = !empty($rows) ? array_shift($rows) : [];

        $colToVar     = $this->buildColToVarMap($headerRow, $hiddenRow, $variables);
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
                foreach ($variables as $varIndex => $varName) {
                    $colKey = $colKeys[$varIndex] ?? null;
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
                foreach ($variables as $varName) {
                    if (!array_key_exists($varName, $userData)) {
                        $userData[$varName] = '';
                    }
                }
            }

            try {
                $document   = $this->generatorService->generate($template, $userData, []);
                $pdfContent = \Storage::disk('public')->get($document->file_path);

                $firstValue  = Str::slug(array_values($userData)[0] ?? 'dokumen');
                $zipFileName = sprintf('%03d-%s.pdf', $count + 1, $firstValue ?: 'dokumen');
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
            $label      = 'Raport-' . ($request->class ?? 'Semua') . '-' . now()->format('Ymd');
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
     *  1. Hidden row (baris 2 berisi "__VAR__nama_variabel") — paling akurat
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

            // ── Prioritas 1: Hidden variable row ─────────────────────────
            $hiddenVal = trim((string) ($hiddenRow[$col] ?? ''));
            if (str_starts_with($hiddenVal, '__VAR__')) {
                $varName = substr($hiddenVal, 7); // hapus prefix "__VAR__"
                if (in_array($varName, $variables)) {
                    $colToVar[$col] = $varName;
                    continue;
                }
            }

            $labelClean = strtolower(trim((string) $label));

            // ── Prioritas 2: Exact slug match ─────────────────────────────
            $slug = strtolower(str_replace([' ', '-'], '_', $labelClean));
            if (in_array($slug, $variables)) {
                $colToVar[$col] = $slug;
                continue;
            }

            // ── Prioritas 3: Normalized partial match ─────────────────────
            // Hapus semua karakter selain huruf dan angka, bandingkan prefix
            $labelNorm = preg_replace('/[^a-z0-9]/', '', $labelClean);

            if (!empty($labelNorm)) {
                $bestMatch    = null;
                $bestScore    = 0;

                foreach ($variables as $varName) {
                    $varNorm = preg_replace('/[^a-z0-9]/', '', strtolower($varName));

                    // Cek apakah salah satu adalah prefix dari yang lain
                    $minLen = min(strlen($labelNorm), strlen($varNorm));
                    if ($minLen < 4) {
                        continue; // terlalu pendek untuk dibandingkan
                    }

                    $labelPrefix = substr($labelNorm, 0, $minLen);
                    $varPrefix   = substr($varNorm, 0, $minLen);

                    if ($labelPrefix === $varPrefix) {
                        // Skor: panjang prefix yang cocok
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
     * Mengonversi angka, boolean, dan null ke string.
     */
    private function sanitizeCellValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        // Tangani nilai numerik: hindari notasi ilmiah
        if (is_float($value) || is_int($value)) {
            // Jika nilai adalah angka bulat besar (mis. NIS/NISN), jaga presisinya
            if (is_float($value) && floor($value) == $value && abs($value) < 1e15) {
                return number_format($value, 0, '.', '');
            }
            return (string) $value;
        }

        return trim((string) $value);
    }

    /**
     * Mengubah nama variabel snake_case menjadi label cantik untuk header Excel.
     * Menghapus suffix acak yang sering muncul dari generator variabel otomatis.
     */
    private function friendlyVarLabel(string $varName): string
    {
        // Hapus suffix angka+huruf acak di akhir (mis: z3ded, 3ded, 2abc)
        $clean = preg_replace('/[a-z]?\d+[a-z]{0,4}$/i', '', $varName);

        // Hapus suffix huruf acak lainnya (mis: islamttze, tahfidzded)
        $clean = preg_replace('/[a-z]{2,6}ttze$/i', '', $clean);
        $clean = preg_replace('/[a-z]{2,6}ded$/i', '',  $clean);

        // Ganti underscore → spasi, title case
        $clean = trim(str_replace('_', ' ', $clean));

        // Fallback jika kosong
        if (empty($clean)) {
            $clean = str_replace('_', ' ', $varName);
        }

        return ucwords($clean);
    }
}