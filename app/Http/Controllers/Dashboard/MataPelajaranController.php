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
        $no = 0;
        $matapelajarans = Pelajaran::all();
        return view('dashboard.matapelajaran.index', compact('matapelajarans', 'no'));
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
    public function destroy(PelajaranActionDelete $pelajaranActionDelete,$slug)
    {
        $pelajaranActionDelete->execute($slug);
        return redirect()->route('dashboard.datasekolah.matapelajaran.index')->with('success', 'Barhasil Menghapus Mata Pelajaran');
    }
}
