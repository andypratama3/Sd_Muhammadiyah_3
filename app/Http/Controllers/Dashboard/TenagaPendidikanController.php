<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\TenagaPendidikan;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\TenagaPendidikanData;
use App\Actions\Dashboard\TenagaPendidikan\TenagaPendidikanAction;
use App\Actions\Dashboard\TenagaPendidikan\TenagaPendidikanActionDelete;

class TenagaPendidikanController extends Controller
{
    public function index()
    {
        $limit = 15;
        $datas = TenagaPendidikan::select(['name','jabatan','foto','slug'])->orderBy('created_at', 'asc')->paginate($limit);
        $count = $datas->count();
        $no = $limit * ($datas->currentPage() - 1);
        return view('dashboard.tenagapendidikan.index', compact('datas','count','no'));
    }
    public function create()
    {
        return view('dashboard.tenagapendidikan.create');
    }
    public function store(TenagaPendidikanData $tenagaPendidikanData, TenagaPendidikanAction $tenagaPendidikanAction)
    {
        $tenagaPendidikanAction->execute($tenagaPendidikanData);
        return redirect()->route('dashboard.datasekolah.tenagapendidikan.index')->with('success','Berhasil Menambah Tenaga Pendidikan');
    }
    public function edit(TenagaPendidikan $tenagaPendidikan)
    {
        return view('dashboard.tenagapendidikan.show', compact('tenagaPendidikan'));
    }
    public function update(TenagaPendidikanData $tenagaPendidikanData, TenagaPendidikanAction $tenagaPendidikanAction)
    {
        $tenagaPendidikanAction->execute($tenagaPendidikanData);
        return redirect()->route('dashboard.datasekolah.tenagapendidikan.index')->with('success','Berhasil Update Tenaga Pendidikan');
    }
    public function destroy(TenagaPendidikanActionDelete $tenagaPendidikanActionDelete,TenagaPendidikan $tenagaPendidikan)
    {
        $tenagaPendidikanActionDelete->execute($tenagaPendidikan);
        return redirect()->route('dashboard.datasekoleh.tenagapendidikan.index')->with('success','Berhasil Menghapus Tenaga Pendidikan');
    }
}
