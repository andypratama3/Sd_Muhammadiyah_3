<?php

namespace App\Http\Controllers\Dashboard\Document;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Student;
use App\Services\DocumentGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentGeneratorService $generatorService
    ) {}

    // =========================================================
    // INDEX
    // =========================================================

    public function index(): View
    {
        $documents = Document::with('template.category', 'student')
            ->latest()
            ->paginate(20);

        return view('dashboard.document.documents.index', compact('documents'));
    }

    // =========================================================
    // CREATE (form isi variabel untuk single doc)
    // =========================================================

    public function create(DocumentTemplate $template): View
    {
        $template->load('category');
        $variables = $template->extractVariables();

        return view('dashboard.document.documents.create', compact('template', 'variables'));
    }

    // =========================================================
    // STORE (single generate → download langsung)
    // =========================================================

    public function store(Request $request, DocumentTemplate $template): Response
    {
        $template->load('category');
        $variables = $template->extractVariables();

        $rules = [];
        foreach ($variables as $var) {
            $rules[$var] = 'nullable|string';
        }

        $userData = $request->validate($rules);

        // $meta = ['created_by' => auth()->id()];
        $meta = [];

        $document = $this->generatorService->generate($template, $userData, $meta);

        $pdfContent = \Storage::disk('public')->get($document->file_path);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="dokumen-' . $document->verification_code . '.pdf"',
        ]);
    }

    // =========================================================
    // BULK GENERATE FORM (pilih template + range siswa)
    // =========================================================

    /**
     * Tampilkan form bulk generate:
     * - Pilih template
     * - Pilih kelas / semua siswa / upload CSV
     */
    public function bulkCreate(): View
    {
        $templates = DocumentTemplate::with('category')->orderBy('name')->get();
        $classes   = Student::distinct()->orderBy('class')->pluck('class');

        return view('dashboard.document.documents.bulk-create', compact('templates', 'classes'));
    }

    /**
     * Preview variabel yang dibutuhkan template yang dipilih (AJAX).
     */
    public function bulkPreviewVariables(Request $request): \Illuminate\Http\JsonResponse
    {
        $template  = DocumentTemplate::findOrFail($request->template_id);
        $variables = $template->extractVariables();

        return response()->json(['variables' => $variables]);
    }

    // =========================================================
    // BULK STORE — generate dari data siswa DB
    // =========================================================

    /**
     * Generate dokumen massal dari data siswa yang ada di database.
     *
     * Request params:
     *  - template_id      : ID template
     *  - scope            : 'class' | 'all' | 'selected'
     *  - class            : nama kelas (jika scope = class)
     *  - student_ids[]    : array ID siswa (jika scope = selected)
     *  - output           : 'separate' (per siswa) | 'merged' (1 PDF)
     *  - field_map[var]   : mapping variabel template → kolom siswa/custom
     *  - static_[var]     : nilai statis untuk variabel yang tidak dari DB
     */
    public function bulkStore(Request $request): mixed
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'scope'       => 'required|in:class,all,selected',
            'output'      => 'required|in:separate,merged',
        ]);

        $template = DocumentTemplate::with('category')->findOrFail($request->template_id);
        $variables = $template->extractVariables();

        // --- Ambil daftar siswa ---
        $query = Student::query();

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

        // --- Mapping kolom siswa ke variabel template ---
        $fieldMap   = $request->input('field_map', []);   // ['nama_siswa' => 'name', 'nis' => 'nis', ...]
        $staticVars = collect($request->all())
            ->filter(fn ($v, $k) => str_starts_with($k, 'static_'))
            ->mapWithKeys(fn ($v, $k) => [str_replace('static_', '', $k) => $v]);

        // Bangun rows
        $rows = $students->map(function (Student $student) use ($variables, $fieldMap, $staticVars) {
            $row = ['_student_id' => $student->id];

            foreach ($variables as $var) {
                if (isset($fieldMap[$var]) && $fieldMap[$var] !== '') {
                    // Ambil dari kolom siswa (support dot notation: profile.address)
                    $row[$var] = data_get($student, $fieldMap[$var]) ?? '';
                } elseif ($staticVars->has($var)) {
                    $row[$var] = $staticVars[$var];
                } else {
                    $row[$var] = '';
                }
            }

            return $row;
        });

        // --- Output ---
        if ($request->output === 'merged') {
            // Satu PDF multi-halaman
            $label    = 'Raport-' . ($request->class ?? 'Semua') . '-' . now()->format('Ymd');
            $filePath = $this->generatorService->generateBulkMergedPdf($template, $rows, $label);

            $pdfContent = \Storage::disk('public')->get($filePath);

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $label . '.pdf"',
            ]);

        } else {
            // PDF terpisah per siswa, rekam ke DB, redirect ke daftar dengan batch filter
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
    }

    // =========================================================
    // BULK STORE dari CSV upload
    // =========================================================

    /**
     * Upload CSV → generate dokumen massal.
     *
     * CSV format: kolom pertama = header, baris berikutnya = data.
     * Header kolom harus sesuai nama variabel template.
     *
     * Contoh CSV:
     *   nama_siswa,nis,kelas,nilai_rata
     *   Andi Pratama,1001,6A,85.5
     *   Budi Santoso,1002,6B,90.0
     */
    public function bulkStoreFromCsv(Request $request): mixed
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'csv_file'    => 'required|file|mimes:csv,txt|max:2048',
            'output'      => 'required|in:separate,merged',
        ]);

        $template = DocumentTemplate::with('category')->findOrFail($request->template_id);

        // Parse CSV
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
            $label    = 'CSV-Bulk-' . now()->format('Ymd-His');
            $filePath = $this->generatorService->generateBulkMergedPdf($template, $rows, $label);

            $pdfContent = \Storage::disk('public')->get($filePath);

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $label . '.pdf"',
            ]);

        } else {
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
    }

    // =========================================================
    // DOWNLOAD (re-download dokumen yang sudah pernah digenerate)
    // =========================================================

    public function download(Document $document): Response
    {
        abort_unless($document->fileExists(), 404, 'File PDF tidak ditemukan.');

        $pdfContent = \Storage::disk('public')->get($document->file_path);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="dokumen-' . $document->verification_code . '.pdf"',
        ]);
    }

    // =========================================================
    // DESTROY
    // =========================================================

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
}