<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\CategoryData;
use App\Actions\Dashboard\Category\CategoryAction;
use App\Actions\Dashboard\Category\categoryDeleteAction;

class CategoryArtikel extends Controller
{
    public function index()
    {
        $no = 0;
        $categorys = Category::select(['name','slug'])->get();
        return view('dashboard.artikel.category.index', compact('categorys','no'));
    }
    public function create()
    {
        return view('dashboard.artikel.category.create');
    }
    public function store(CategoryData $categoryData, CategoryAction $categoryAction)
    {
        $categoryAction->execute($categoryData);
        return redirect()->route('dashboard.news.category.index')->with('success','Berhasil Menambhakan Category Artikel');
    }
    public function edit(Category $category)
    {
        return view('dashboard.artikel.category.edit', compact('category'));
    }
    public function update(CategoryData $categoryData, CategoryAction $categoryAction)
    {
        $categoryAction->execute($categoryData);
        return redirect()->route('dashboard.news.category.index')->with('success','Berhasil Update Category Artikel');
    }
    public function destroy(Category $category, categoryDeleteAction $categoryDeleteAction)
    {
        $categoryDeleteAction->execute($category);
        return redirect()->route('dashboard.news.category.index')->with('success','Berhasil Manghapus Category Artikel');

    }
}
