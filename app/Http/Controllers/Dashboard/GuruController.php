<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Dashboard\Guru\StoreGuruAction;
use App\Actions\Dashboard\Guru\DeleteGuruAction;
use App\Actions\Dashboard\Guru\UpdateGuruAction;
use App\Http\Requests\Dashboard\StoreGuruRequest;
use App\Http\Requests\Dashboard\UpdateGuruRequest;

class GuruController extends Controller
{
    public function index()
    {
        $limit = 15;
        $gurus = Guru::select(['name','description','lulusan','foto','slug'])->paginate($limit);
        $count = $gurus->count();
        $no = $limit * ($gurus->currentPage() - 1);
        return view('dashboard.guru.index', compact('gurus','count','no'));
    }
    public function create()
    {
        return view('dashboard.guru.create');
    }
    public function store(StoreGuruRequest $request, StoreGuruAction $storeGuruAction)
    {
        $storeGuruAction->execute($request);
        return redirect()->route('dashboard.guru.index')->with('success','Berhasil Menambahkan Guru!');
    }
    public function edit($slug)
    {
        $guru = Guru::where('slug',$slug)->firstOrFail();
        return view('dashboard.guru.edit', compact('guru'));
    }
    public function update(UpdateGuruRequest $request, UpdateGuruAction $updateGuruAction, $slug)
    {
        $updateGuruAction->execute($request, $slug);
        return redirect()->route('dashboard.guru.index')->with('success','Berhasil Update Guru!');
    }
    public function destroy(DeleteGuruAction $DeleteGuruAction, $slug)
    {
        $DeleteGuruAction->execute($slug);
        return redirect()->route('dashboard.guru.index')->with('Berhasil Hapus Guru!');
    }

}
