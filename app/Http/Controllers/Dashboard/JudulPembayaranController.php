<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\JudulPembayaran;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\JudulPembayaranData;
use App\Actions\Dashboard\JudulPembayaran\JudulPembayaranAction;
use App\Actions\Dashboard\JudulPembayaran\JudulPembayaranActionDelete;

class JudulPembayaranController extends Controller
{
    public function index()
    {
        $limit = 15;
        $judul_pembayarans = JudulPembayaran::select(['name','slug'])->paginate($limit);
        $count = $judul_pembayarans->count();
        $no = $limit * ($judul_pembayarans->currentPage() - 1);
        return view('dashboard.data.judulpembayaran.index', compact('judul_pembayarans','count','no'));
    }
    public function create()
    {
        return view('dashboard.data.judulpembayaran.create');
    }
    public function store(JudulPembayaranData $judulPembayaranData,JudulPembayaranAction $judulPembayaranAction)
    {
        $judulPembayaranAction->execute($judulPembayaranData);
        return redirect()->route('dashboard.datamaster.judul.pembayaran.index')->with('success', 'Berhasil Menambahkan Kategori Pembayaran');
    }
    public function edit($slug)
    {
        $judulPembayaran = JudulPembayaran::where('slug', $slug)->firstOrFail();
        return view('dashboard.data.judulpembayaran.edit', compact('judulPembayaran'));
    }
    public function update(JudulPembayaranData $judulPembayaranData,JudulPembayaranAction $judulPembayaranAction)
    {
        $judulPembayaranAction->execute($judulPembayaranData);
        return redirect()->route('dashboard.datamaster.judul.pembayaran.index')->with('success', 'Berhasil Update Kategori Pembayaran');
    }
    public function destroy(JudulPembayaranActionDelete $JudulPembayaranActionDelete,$judulPembayaran)
    {
        $JudulPembayaranActionDelete->execute($judulPembayaran);
        return redirect()->route('dashboard.datamaster.judul.pembayaran.index')->with('success', 'Berhasil Hapus Kategori Pembayaran');

    }
}
