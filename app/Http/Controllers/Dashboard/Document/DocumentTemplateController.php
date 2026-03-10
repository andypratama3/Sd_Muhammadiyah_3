<?php

namespace App\Http\Controllers\Dashboard\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\DocumentTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentTemplateController extends Controller
{
    // =========================================================
    // INDEX
    // =========================================================

    public function index(): View
    {
        $templates = DocumentTemplate::with('category')
            ->withCount('documents')   // pakai $tpl->documents_count di blade, bukan ->documents->count()
            ->latest()
            ->paginate(15);

        return view('dashboard.document.templates.index', compact('templates'));
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function create(): View
    {
        $categories = DocumentCategory::orderBy('name')->get();

        return view('dashboard.document.templates.create', compact('categories'));
    }

    // =========================================================
    // STORE
    // =========================================================

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'   => 'required|exists:document_categories,id',
            'name'          => 'required|string|max:255',
            'html_template' => 'required|string',
            'canvas_json'   => 'nullable|string',
        ]);

        // canvas_json dikirim sebagai string JSON dari JS,
        // decode dulu agar model cast array bekerja benar.
        if (!empty($validated['canvas_json'])) {
            $decoded = json_decode($validated['canvas_json'], true);
            $validated['canvas_json'] = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : null;
        }

        $template = DocumentTemplate::create($validated);

        return redirect()
            ->route('dashboard.documents.templates.edit', $template)
            ->with('success', 'Template berhasil dibuat. Anda bisa melanjutkan pengeditan.');
    }

    // =========================================================
    // EDIT
    // =========================================================

    public function edit(DocumentTemplate $template): View
    {
        $categories = DocumentCategory::orderBy('name')->get();
        $variables  = $template->extractVariables();

        return view('dashboard.document.templates.edit', compact('template', 'categories', 'variables'));
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update(Request $request, DocumentTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'   => 'required|exists:document_categories,id',
            'name'          => 'required|string|max:255',
            'html_template' => 'required|string',
            'canvas_json'   => 'nullable|string',
        ]);

        // Sama seperti store: decode JSON string → array
        if (!empty($validated['canvas_json'])) {
            $decoded = json_decode($validated['canvas_json'], true);
            $validated['canvas_json'] = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : $template->canvas_json; // fallback ke nilai lama jika JSON rusak
        }

        $template->update($validated);

        return redirect()
            ->route('dashboard.documents.templates.edit', $template)
            ->with('success', 'Template berhasil diperbarui.');
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function destroy(DocumentTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()
            ->route('dashboard.documents.templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    // =========================================================
    // AJAX: Preview variables dari canvas_json / html_template
    // Dipakai oleh builder UI via fetch() dan bulk-create page.
    // =========================================================

    /**
     * Terima template_id ATAU raw html string.
     *
     * GET  ?template_id=5          → ambil dari DB
     * POST { html: "..." }         → parse dari raw HTML
     */
    public function previewVariables(Request $request): \Illuminate\Http\JsonResponse
    {
        // Prioritas: jika ada template_id, ambil dari DB
        if ($request->filled('template_id')) {
            $template  = DocumentTemplate::findOrFail($request->integer('template_id'));
            $variables = $template->extractVariables();

            return response()->json(['variables' => $variables]);
        }

        // Fallback: parse dari raw HTML yang dikirim
        $html = $request->input('html', '');
        preg_match_all('/\{\{(.*?)\}\}/', $html, $matches);

        $reserved  = ['logo', 'barcode_signature'];
        $variables = array_values(
            array_filter(
                array_unique(array_map('trim', $matches[1])),
                fn ($v) => !in_array($v, $reserved)
            )
        );

        return response()->json(['variables' => $variables]);
    }
}