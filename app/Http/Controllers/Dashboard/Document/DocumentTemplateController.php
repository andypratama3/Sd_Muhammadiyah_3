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
    public function index(): View
    {
        $templates = DocumentTemplate::with('category')
            ->withCount('documents')
            ->latest()
            ->paginate(15);

        return view('dashboard.document.templates.index', compact('templates'));
    }

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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'   => 'required|exists:document_categories,id',
            'name'          => 'required|string|max:255',
            'html_template' => 'required|string',
            'canvas_json'   => 'nullable|string',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'mapel_ids'     => 'nullable|array',
            'mapel_ids.*'   => 'exists:pelajarans,id',
            // FIX: tambah validasi generate_mode
            'generate_mode' => 'nullable|in:perorang,daftar',
        ]);

        if (!empty($validated['canvas_json'])) {
            $decoded = json_decode($validated['canvas_json'], true);
            $validated['canvas_json'] = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : null;
        }

        // Default generate_mode ke 'perorang' jika tidak dikirim
        $validated['generate_mode'] = $validated['generate_mode'] ?? 'perorang';

        // Hanya set kelas_id untuk kategori Rapot
        $category = DocumentCategory::find($validated['category_id']);
        if ($category && strtolower($category->name) !== 'rapot') {
            $validated['kelas_id'] = null;
        }

        $template = DocumentTemplate::create($validated);

        $template->pelajarans()->sync($request->input('mapel_ids', []));

        return redirect()
            ->route('dashboard.documents.templates.edit', $template)
            ->with('success', 'Template berhasil dibuat. Anda bisa melanjutkan pengeditan.');
    }

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

    public function update(Request $request, DocumentTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'   => 'required|exists:document_categories,id',
            'name'          => 'required|string|max:255',
            'html_template' => 'required|string',
            'canvas_json'   => 'nullable|string',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'mapel_ids'     => 'nullable|array',
            'mapel_ids.*'   => 'exists:pelajarans,id',
            // FIX: tambah validasi generate_mode
            'generate_mode' => 'nullable|in:perorang,daftar',
        ]);

        if (!empty($validated['canvas_json'])) {
            $decoded = json_decode($validated['canvas_json'], true);
            $validated['canvas_json'] = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : $template->canvas_json;
        }

        // Default generate_mode ke nilai lama jika tidak dikirim
        $validated['generate_mode'] = $validated['generate_mode'] ?? $template->generate_mode ?? 'perorang';

        $category = DocumentCategory::find($request->input('category_id'));

        if ($category && strtolower($category->name) === 'rapot') {
            $validated['kelas_id'] = $request->input('kelas_id');
        } else {
            $validated['kelas_id'] = null;
        }

        $template->update($validated);

        $template->pelajarans()->sync($request->input('mapel_ids', []));

        return redirect()
            ->route('dashboard.documents.templates.edit', $template)
            ->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(DocumentTemplate $template): RedirectResponse
    {
        try {
            $template->kelasList()->detach();
        } catch (\Throwable $e) {
        }

        $template->pelajarans()->detach();
        $template->delete();

        return redirect()
            ->route('dashboard.documents.templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

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

    public function getAvailableVariables(): \Illuminate\Http\JsonResponse
    {
        return response()->json(TemplateVariableRegistry::getGrouped());
    }

    public function apiKelasList(): \Illuminate\Http\JsonResponse
    {
        $kelasList = Kelas::orderBy('name')
            ->get(['id', 'name', 'slug', 'category_kelas']);

        return response()->json($kelasList);
    }

    public function apiMapelList(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Pelajaran::orderBy('name');

        if ($request->filled('kelas_id')) {
            $query->whereHas('kelasPelajaran', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        $mapelList = $query->get(['id', 'name', 'slug']);

        return response()->json($mapelList);
    }
}