<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\PembayaranData;
use App\Actions\Dashboard\Pembayaran\PembayaranAction;
use App\Actions\Dashboard\Pembayaran\PembayaranActionDelete;

class PembayaranController extends Controller
{
    public function index()
    {
        $no = 0;
        $pembayarans = Pembayaran::all();
        return view('dashboard.data.pembayaran.index', compact('no','pembayarans'));
    }
    public function data_table()
    {
        // $query = Pembayaran::select([''])
    }
    public function create()
    {
        $siswas = Siswa::select(['id','name','slug'])->get();
        $kelass = Kelas::orderBy('name','asc')->get();
        return view('dashboard.data.pembayaran.create', compact('siswas','kelass'));
    }
    public function store(PembayaranData $pembayaranData, PembayaranAction $pembayaranAction)
    {
        $pembayaranAction->execute($pembayaranData);
        return redirect()->route('dashboard.datamaster.pembayaran.index')->with('success', 'Berhasil Menambahkan Pembayaran');
    }
    public function show(Pembayaran $pembayaran)
    {
        return view('dashboard.data.pembayaran.show', compact('pembayaran'));
    }
    public function edit($order_id)
    {
        $pembayaran = Pembayaran::where('order_id', $order_id)->firstOrFail();
        $siswas = Siswa::select(['id','name','slug'])->get();
        $kelass = Kelas::orderBy('name','asc')->get();
        return view('dashboard.data.pembayaran.edit', compact('siswas','kelass','pembayaran'));
    }
    public function update(PembayaranData $pembayaranData, PembayaranAction $pembayaranAction)
    {
        $pembayaranAction->execute($pembayaranData);
        return redirect()->route('dashboard.datamaster.pembayaran.index')->with('success', 'Berhasil Update Pembayaran');
    }
    public function destroy(PembayaranActionDelete $pembayaranActionDelete, Pembayaran $pembayaran)
    {
        $pembayaranActionDelete->execute($pembayaran);
        return redirect()->route('dashboard.datamaster.pembayaran.index')->with('success', 'Berhasil Menghapus Pembayaran');
    }
}
