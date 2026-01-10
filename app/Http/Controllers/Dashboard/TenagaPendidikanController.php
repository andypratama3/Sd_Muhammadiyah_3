<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\TenagaPendidikan;
use App\Http\Controllers\Controller;
use App\Models\StrukturTenagaPendidikan;
use App\DataTransferObjects\TenagaPendidikanData;
use App\Actions\Dashboard\TenagaPendidikan\TenagaPendidikanAction;
use App\Actions\Dashboard\TenagaPendidikan\TenagaPendidikanActionDelete;

class TenagaPendidikanController extends Controller
{
    public function index()
    {
        $limit = 15;
        $datas = TenagaPendidikan::with('struktur_tenaga_pendidikan')->select(['name', 'jabatan', 'foto', 'slug','struktur_tenaga_pendidikan_id'])->orderBy('created_at', 'asc')->paginate($limit);
        $count = $datas->count();
        $no = $limit * ($datas->currentPage() - 1);

        return view('dashboard.tenagapendidikan.index', compact('datas', 'count', 'no'));
    }

    public function create()
    {
        $strukturTenagaPendidikan = StrukturTenagaPendidikan::orderBy('name', 'asc')->get();
        return view('dashboard.tenagapendidikan.create', compact('strukturTenagaPendidikan'));
    }

    public function store(TenagaPendidikanData $tenagaPendidikanData, TenagaPendidikanAction $tenagaPendidikanAction)
    {
        $tenagaPendidikanAction->execute($tenagaPendidikanData);

        return redirect()->route('dashboard.datasekolah.tenagapendidikan.index')->with('success', 'Berhasil Menambah Tenaga Pendidikan');
    }

    public function show(TenagaPendidikan $tenagaPendidikan)
    {
        return view('dashboard.tenagapendidikan.show', compact('tenagaPendidikan'));
    }

    public function edit(TenagaPendidikan $tenagapendidikan)
    {
        $strukturTenagaPendidikan = StrukturTenagaPendidikan::orderBy('name', 'asc')->get();
        return view('dashboard.tenagapendidikan.edit', compact('tenagapendidikan','strukturTenagaPendidikan'));
    }

    public function update(TenagaPendidikanData $tenagaPendidikanData, TenagaPendidikanAction $tenagaPendidikanAction)
    {
        $tenagaPendidikanAction->execute($tenagaPendidikanData);

        return redirect()->route('dashboard.datasekolah.tenagapendidikan.index')->with('success', 'Berhasil Update Tenaga Pendidikan');
    }

    public function destroy(TenagaPendidikanActionDelete $tenagaPendidikanActionDelete, $id)
    {

        $tenagaPendidikanActionDelete->execute($id);

        return redirect()->route('dashboard.datasekolah.tenagapendidikan.index')->with('success', 'Berhasil Menghapus Tenaga Pendidikan');
    }
}
