<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Prestasi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\PrestasiData;
use App\Actions\Dashboard\Prestasi\PrestasiAction;
use App\Actions\Dashboard\Prestasi\PrestasiActionDelete;

class PrestasiController extends Controller
    {
    /**
    *   ? data Status
    *   ! status 1 prestasi Siswa
    *   ! status 2 prestasi Sekolah
    */
    public function index()
    {
        $limit = 15;
        $prestasis = Prestasi::select(['name','description','status','slug'])->paginate($limit);
        $count = $prestasis->count();
        $no = $limit * ($prestasis->currentPage() - 1);
        return view('dashboard.prestasi.index' ,compact('prestasis', 'count', 'no'));
    }
    public function create()
    {
        return view('dashboard.prestasi.create');
    }
    public function store(PrestasiData $prestasiData, PrestasiAction $prestasiAction)
    {
        $prestasiAction->execute($prestasiData);
        return redirect()->route('dashboard.datasekolah.prestasi.index')->with('success','Prestasi Berhasil Di Tambahkan');
    }
    public function show(Prestasi $prestasi)
    {
        return view('dashboard.prestasi.show', compact('prestasi'));
    }
    public function edit(Prestasi $prestasi)
    {
        return view('dashboard.prestasi.edit', compact('prestasi'));
    }
    public function update(PrestasiData $prestasiData, PrestasiAction $prestasiAction)
    {
        $prestasiAction->execute($prestasiData);
        return redirect()->route('dashboard.datasekolah.prestasi.index')->with('success','Prestasi Berhasil Di Update');
    }
    public function destroy(PrestasiActionDelete $prestasiActionDelete,$slug)
    {
        $prestasiActionDelete->execute($slug);
        return redirect()->route('dashboard.datasekolah.prestasi.index')->with('success','Prestasi Berhasil Di Hapus');
    }
}
