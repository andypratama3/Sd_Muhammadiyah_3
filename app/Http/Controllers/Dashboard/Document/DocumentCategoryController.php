<?php

namespace App\Http\Controllers\Dashboard\Document;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentCategoryController extends Controller
{
    public function index(): View
    {
        $categories = DocumentCategory::withCount('templates')->latest()->paginate(12);
        return view('dashboard.document.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('dashboard.document.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        DocumentCategory::create($data);

        return redirect()->route('dashboard.documents.categories.index')->with('success', 'Document category created successfully.');
    }

    public function edit(DocumentCategory $category): View
    {
        return view('dashboard.document.categories.edit', compact('category'));
    }

    public function update(Request $request, DocumentCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($category->logo_path) {
                \Storage::disk('public')->delete($category->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        $category->update($data);

        return redirect()->route('dashboard.documents.categories.index')->with('success', 'Document category updated successfully.');
    }

    public function destroy(DocumentCategory $category): RedirectResponse
    {
        if ($category->logo_path) {
            \Storage::disk('public')->delete($category->logo_path);
        }

        $category->delete();

        return redirect()->route('dashboard.documents.categories.index')->with('success', 'Category deleted.');
    }
}
