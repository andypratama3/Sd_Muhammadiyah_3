<?php

namespace App\Http\Controllers\Dashboard\Absensi;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Models\PengajuanCuti;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengajuanCutiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ✅ Superadmin & Admin lihat semua
        if ($user->hasAnyRole(['superadmin', 'admin'])) {

            $pengajuanCuti = PengajuanCuti::with('karyawan')
                ->latest()
                ->get();

        } else {

            // ✅ User biasa hanya lihat cutinya sendiri
            $karyawan = $user->karyawan;

            if (!$karyawan) {
                abort(403, 'Data karyawan tidak ditemukan');
            }

            $pengajuanCuti = PengajuanCuti::with('karyawan')
                ->where('karyawan_id', $karyawan->id)
                ->latest()
                ->get();
        }

        return view('dashboard.absensis.pengajuan_cuti.index',compact('pengajuanCuti')
        );
    }


    public function create()
    {
        return view('dashboard.absensis.pengajuan_cuti.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis'            => 'required|string',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_hari'      => 'required|integer|min:1',
            'alasan'           => 'required|string',
            'file_pendukung'   => 'nullable|file|max:2048',
        ]);

        if ($request->hasFile('file_pendukung')) {
            $data['file_pendukung'] = $request->file('file_pendukung')
                ->store('cuti', 'public');
        }

        $karyawan = Auth::user()->karyawan;

        if (!$karyawan) {
            return back()->withErrors(['karyawan' => 'Data karyawan tidak ditemukan']);
        }



        $data['karyawan_id'] = $karyawan->id;

        $data['status'] = 'menunggu';

        PengajuanCuti::create($data);

        return redirect()
            ->route('dashboard.pengajuan_cuti.index')
            ->with('success', 'Pengajuan cuti berhasil dikirim');
    }

    public function edit($id)
    {
        $pengajuanCuti = PengajuanCuti::findOrFail($id);
        return view('dashboard.absensis.pengajuan_cuti.edit',compact('pengajuanCuti'));
    }

    public function update(Request $request, $id)
    {
        $pengajuanCuti = PengajuanCuti::findOrFail($id);
        $rules = [
            'jenis'            => 'required|string',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_hari'      => 'required|integer|min:1',
            'alasan'           => 'required|string',
            'file_pendukung'   => 'nullable|file|max:2048',
        ];

        // ✅ Hanya admin bisa ubah status
        if (Auth::user()->hasAnyRole(['admin','superadmin'])) {
            $rules['status'] = 'required|in:menunggu,disetujui,ditolak';
            $rules['catatan_admin'] = 'nullable|string';
        }

        $data = $request->validate($rules);

        // upload file
        if ($request->hasFile('file_pendukung')) {
            if ($pengajuanCuti->file_pendukung) {
                Storage::disk('public')->delete($pengajuanCuti->file_pendukung);
            }

            $data['file_pendukung'] = $request->file('file_pendukung')
                ->store('cuti', 'public');
        }

        // ✅ jika di-approve / ditolak
        if (isset($data['status']) && $data['status'] !== 'menunggu') {
            $data['disetujui_oleh'] = Auth::id();
        }

        $pengajuanCuti->update($data);

        return redirect()
            ->route('dashboard.pengajuan_cuti.index')
            ->with('success', 'Pengajuan cuti berhasil diperbarui');
    }
    
    public function destroy(PengajuanCuti $pengajuanCuti)
    {
        if ($pengajuanCuti->file_pendukung) {
            Storage::disk('public')->delete($pengajuanCuti->file_pendukung);
        }

        $pengajuanCuti->delete();

        return redirect()
            ->route('dashboard.pengajuan_cuti.index')
            ->with('success', 'Pengajuan cuti berhasil dihapus');
    }   
}
