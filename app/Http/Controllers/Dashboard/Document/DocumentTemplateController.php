<?php

namespace App\Http\Controllers\Dashboard\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\DocumentTemplate;
use App\Models\Kelas;
use App\Models\Pelajaran;
use App\Services\TemplateVariableRegistry;
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
            ->withCount('documents')
            ->latest()
            ->paginate(15);

        return view('dashboard.document.templates.index', compact('templates'));
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function create(): View
    {
        $categories     = DocumentCategory::orderBy('name')->get();
        $variableGroups = TemplateVariableRegistry::getGrouped();
        $kelasList      = Kelas::orderBy('name')->get();
        $mapelList      = Pelajaran::orderBy('name')->get();

        return view('dashboard.document.templates.create', compact(
            'categories', 'variableGroups', 'kelasList', 'mapelList'
        ));
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
            'kelas_ids'     => 'nullable|array',
            'kelas_ids.*'   => 'exists:kelas,id',
            'mapel_ids'     => 'nullable|array',
            'mapel_ids.*'   => 'exists:pelajarans,id',
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

        // Sync many-to-many
        if ($request->has('kelas_ids')) {
            $template->kelasList()->sync($request->input('kelas_ids', []));
        }
        if ($request->has('mapel_ids')) {
            $template->pelajarans()->sync($request->input('mapel_ids', []));
        }

        return redirect()
            ->route('dashboard.documents.templates.edit', $template)
            ->with('success', 'Template berhasil dibuat. Anda bisa melanjutkan pengeditan.');
    }

    // =========================================================
    // EDIT
    // =========================================================

    public function edit(DocumentTemplate $template): View
    {
        $template->load(['kelasList', 'pelajarans']);

        $categories     = DocumentCategory::orderBy('name')->get();
        $variables      = $template->extractVariables();
        $variableGroups = TemplateVariableRegistry::getGrouped();
        $kelasList      = Kelas::orderBy('name')->get();
        $mapelList      = Pelajaran::orderBy('name')->get();

        return view('dashboard.document.templates.edit', compact(
            'template', 'categories', 'variables', 'variableGroups', 'kelasList', 'mapelList'
        ));
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
            'kelas_ids'     => 'nullable|array',
            'kelas_ids.*'   => 'exists:kelas,id',
            'mapel_ids'     => 'nullable|array',
            'mapel_ids.*'   => 'exists:pelajarans,id',
        ]);

        // Sama seperti store: decode JSON string → array
        if (!empty($validated['canvas_json'])) {
            $decoded = json_decode($validated['canvas_json'], true);
            $validated['canvas_json'] = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : $template->canvas_json; // fallback ke nilai lama jika JSON rusak
        }

        $template->update($validated);

        // Sync many-to-many
        $template->kelasList()->sync($request->input('kelas_ids', []));
        $template->pelajarans()->sync($request->input('mapel_ids', []));

        return redirect()
            ->route('dashboard.documents.templates.edit', $template)
            ->with('success', 'Template berhasil diperbarui.');
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function destroy(DocumentTemplate $template): RedirectResponse
    {
        $template->kelasList()->detach();
        $template->pelajarans()->detach();
        $template->delete();

        return redirect()
            ->route('dashboard.documents.templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    // =========================================================
    // AJAX: Preview variables dari canvas_json / html_template
    // =========================================================

    public function previewVariables(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->filled('template_id')) {
            $template  = DocumentTemplate::findOrFail($request->integer('template_id'));
            $variables = $template->extractVariables();

            return response()->json(['variables' => $variables]);
        }

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

    // =========================================================
    // AJAX: All available variables grouped (for template editor)
    // =========================================================

    public function getAvailableVariables(): \Illuminate\Http\JsonResponse
    {
        return response()->json(TemplateVariableRegistry::getGrouped());
    }

    // =========================================================
    // API: Kelas list for template editor JS
    // =========================================================

    public function apiKelasList(): \Illuminate\Http\JsonResponse
    {
        $kelasList = Kelas::orderBy('name')
            ->get(['id', 'name', 'slug', 'category_kelas']);

        return response()->json($kelasList);
    }

    // =========================================================
    // API: Pelajaran list for template editor JS
    // =========================================================

    public function apiMapelList(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Pelajaran::orderBy('name');

        $mapelList = $query->get(['id', 'name', 'slug']);

        return response()->json($mapelList);
    }
}