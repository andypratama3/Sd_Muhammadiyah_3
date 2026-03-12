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
   
    public function index(): View
    {
        $documents = Document::with('template.category', 'student')
            ->latest()
            ->paginate(20);

        return view('dashboard.document.documents.index', compact('documents'));
    }

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

    public function excelTemplate(DocumentTemplate $template): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $template->load('category');
        $variables = $template->extractVariables();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

    
        foreach ($variables as $colIndex => $var) {
            $col  = $colIndex + 1;
            $cell = Coordinate::stringFromColumnIndex($col) . '1';
            // $label = ucwords(str_replace('_', ' ', $var));
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
        $sheet->freezePane('A2');

    
        foreach ($variables as $colIndex => $var) {
            $col  = $colIndex + 1;
            $cell = Coordinate::stringFromColumnIndex($col) . '2';
            $sheet->setCellValue($cell, 'Contoh ' . ucwords(str_replace('_', ' ', $var)));
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['italic' => true, 'color' => ['argb' => 'FF9E9E9E'], 'name' => 'Arial'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F5F5']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }
        $sheet->getRowDimension(2)->setRowHeight(20);

    
        $info = $spreadsheet->createSheet();
        $info->setTitle('Petunjuk');
        $info->setCellValue('A1', 'PETUNJUK PENGISIAN');
        $info->setCellValue('A3', '1. Isi data mulai dari baris ke-3 pada sheet "Data" (baris 1 = header, baris 2 = contoh yang bisa dihapus).');
        $info->setCellValue('A4', '2. Setiap baris = satu dokumen PDF yang akan digenerate.');
        $info->setCellValue('A5', '3. Jangan mengubah nama kolom pada baris header.');
        $info->setCellValue('A6', '4. Upload file ini kembali di halaman Generate untuk memproses semua data sekaligus.');
        $info->setCellValue('A8', 'Template : ' . $template->name);
        $info->setCellValue('A9', 'Kategori : ' . $template->category->name);
        $info->setCellValue('A10', 'Dibuat   : ' . now()->format('d M Y H:i'));
        $info->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $info->getStyle('A8:A10')->getFont()->setItalic(true)->getColor()->setARGB('FF757575');
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

        // First row = headers
        $headerRow = array_shift($rows);
        $colToVar  = [];
        foreach ($headerRow as $col => $label) {
            if (empty($label)) continue;
            $slug = strtolower(str_replace(' ', '_', trim((string) $label)));
            if (in_array($slug, $variables)) {
                $colToVar[$col] = $slug;
            }
        }

        $result = [];
        $index  = 1;
        foreach ($rows as $row) {
            // Skip empty rows
            $values = array_filter(array_values($row), fn($v) => $v !== null && $v !== '');
            if (empty($values)) continue;

            // Skip the example row generated by excelTemplate()
            $firstVal = (string) array_values($row)[0];
            if (str_starts_with($firstVal, 'Contoh ')) continue;

            $data = [];
            foreach ($colToVar as $col => $varName) {
                $data[$varName] = isset($row[$col]) ? (string) $row[$col] : '';
            }

            // Use first data value as a human-readable label for the UI
            $firstData = array_values($data)[0] ?? '';
            $label     = $firstData ?: 'Baris ' . $index;
            $filename  = \Illuminate\Support\Str::slug($label) ?: 'dokumen-' . $index;

            $result[] = [
                'label'    => $label,
                'filename' => $filename,
                'data'     => $data,
            ];

            $index++;
        }

        return response()->json(['rows' => $result]);
    }

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
        $colToVar  = [];
        foreach ($headerRow as $col => $label) {
            if (empty($label)) continue;
            $slug = strtolower(str_replace(' ', '_', trim((string) $label)));
            if (in_array($slug, $variables)) {
                $colToVar[$col] = $slug;
            }
        }

    
        $zipPath = sys_get_temp_dir() . '/batch_' . uniqid() . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $count = 0;
        foreach ($rows as $rowNum => $row) {
        
            $values = array_filter(array_values($row), fn ($v) => $v !== null && $v !== '');
            if (empty($values)) continue;

        
            $firstVal = (string) array_values($row)[0];
            if (str_starts_with($firstVal, 'Contoh ')) continue;

        
            $userData = [];
            foreach ($colToVar as $col => $varName) {
                $userData[$varName] = isset($row[$col]) ? (string) $row[$col] : '';
            }

            try {
                $document   = $this->generatorService->generate($template, $userData, []);
                $pdfContent = \Storage::disk('public')->get($document->file_path);

            
                $firstValue = Str::slug(array_values($userData)[0] ?? 'dokumen');
                $zipFileName = sprintf('%03d-%s.pdf', $count + 1, $firstValue);
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
            ->filter(fn ($v, $k) => str_starts_with($k, 'static_'))
            ->mapWithKeys(fn ($v, $k) => [str_replace('static_', '', $k) => $v]);

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
            ->map(fn (Siswa $s) => [
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
    private function friendlyVarLabel(string $varName): string
    {
        // Hapus suffix angka+huruf acak di akhir (misal: "islamttze", "z3ded", "3ded")
        // Pattern: hapus segmen terakhir yang mengandung angka atau huruf acak (bukan kata bermakna)
        $clean = preg_replace('/[a-z]?\d+[a-z]{0,4}$/i', '', $varName); // hapus suffix seperti z3ded, 3ded
        $clean = preg_replace('/[a-z]{2,6}ttze$/i', '', $clean);         // hapus suffix seperti "islamttze"
        $clean = preg_replace('/[a-z]{2,6}ded$/i', '', $clean);          // hapus suffix seperti "tahfidzded"

        // Ganti underscore → spasi, lalu title case
        $clean = trim(str_replace('_', ' ', $clean));
        $clean = trim($clean);

        // Fallback: jika setelah dibersihkan kosong, pakai nama asli
        if (empty($clean)) {
            $clean = str_replace('_', ' ', $varName);
        }

        return ucwords($clean);
    }
}