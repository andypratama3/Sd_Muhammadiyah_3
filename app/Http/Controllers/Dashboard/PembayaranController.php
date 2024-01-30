<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('dashboard.data.pembayaran.index');
    }
    public function create()
    {
        $siswas = Siswa::select(['id','name','slug'])->get();
        return view('dashboard.data.pembayaran.create', compact('siswas'));
    }
    public function store(PembayaranData $pembayaranData, PembayaranAction $pembayaranAction)
    {
        $midtrans_data = $pembayaranData;

        $pembayaranAction->execute($pembayaranData);
        return redirect()->route('dashboard.datamaster.pembayaran.index')->with('success', 'Berhasil Menambahkan Pembayaran');
    }
    public function show(Pembayaran $pembayaran)
    {
        return view('dashboard.data.pembayaran.show', compact('pembayaran'));
    }
    public function edit(Pembayaran $pembayaran)
    {
        return view('dashboard.data.pembayaran.edit', compact('pembayaran'));
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
