<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Hero;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\HeroData;
use App\Actions\Dashboard\Hero\HeroAction;

class HeroController extends Controller
{
    public function index()
    {
        $limit = 15;
        $heroes = Hero::latest()->paginate($limit);
        $count = $heroes->count();
        $no = $limit * ($heroes->currentPage() - 1);
        return view('dashboard.hero.index', compact('heroes','count','no'));
    }

    public function create()
    {
        return view('dashboard.hero.create');
    }

    public function store(HeroData $heroData, HeroAction $heroAction)
    {
        $heroAction->execute($heroData);
        return redirect()->route('dashboard.news.hero.index')->with('succes','Berhasil Menambahkan Hero');
    }

    public function edit(Hero $hero)
    {
       return view('dashboard.hero.edit', compact('hero'));
    }

    public function update(HeroData $heroData, HeroAction $heroAction)
    {
        $heroAction->execute($heroData);
        return redirect()->route('dashboard.news.hero.index')->with('succes','Berhasil Mengedit Hero');
    }

    public function destroy(Hero $hero)
    {
        $hero->delete();
        return redirect()->route('dashboard.news.hero.index')->with('succes','Berhasil Menghapus Hero');
    }


}
