<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Pelajaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\PelajaranData;
use App\Actions\Dashboard\Pelajaran\PelajaranAction;
use App\Actions\Dashboard\Pelajaran\PelajaranActionDelete;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $limit = 10;

        $matapelajarans = Pelajaran::orderBy('created_at', 'desc')->paginate($limit);
        $no = $limit * ($matapelajarans->currentPage() - 1);
        $count = $matapelajarans->count();
        return view('dashboard.matapelajaran.index', compact('matapelajarans', 'no', 'count'));
    }
    public function create()
    {
        return view('dashboard.matapelajaran.create');
    }
    public function store(PelajaranData $pelajaranData, PelajaranAction $PelajaranAction)
    {
        $PelajaranAction->execute($pelajaranData);
        return redirect()->route('dashboard.datasekolah.matapelajaran.index')->with('success','Berhasil Menambah MataPelajaran');
    }

    public function edit($slug)
    {
        $matapelajaran = Pelajaran::where('slug', $slug)->firstOrFail();
        return view('dashboard.matapelajaran.edit', compact('matapelajaran'));
    }

    public function update(PelajaranAction $PelajaranAction, PelajaranData $pelajaranData)
    {
        $PelajaranAction->execute($pelajaranData);


        return redirect()->route('dashboard.datasekolah.matapelajaran.index')->with('success', 'Berhasil Mengubah Mata Pelajaran');
    }

    public function destroy(PelajaranActionDelete $pelajaranActionDelete,$slug)
    {

        $pelajaranActionDelete->execute($slug);
        return redirect()->route('dashboard.datasekolah.matapelajaran.index')->with('success', 'Barhasil Menghapus Mata Pelajaran');
    }
}
