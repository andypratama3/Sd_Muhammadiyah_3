<?php
namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class PengisianOrangTuaController extends Controller
{
    public function index()
    {
        $siswas = Siswa::whereHas('kelas', function ($q) {
            $q->where('name', '!=', 'Lulus');
        })
        ->orderBy('name','asc')
        ->get();

        return view('pengisian_orang_tua.nomor_hp', compact('siswas'));
    }

    public function show($nisn)
    {
        $siswa = Siswa::where('nisn', $nisn)->firstOrFail();

        return response()->json([
            'status' => 'success',
            'nama_ayah' => $siswa->nama_ayah,
            'nama_ibu' => $siswa->nama_ibu,
            'wali' => $siswa->nama_wali,
        ]);
    }

    public function verifikasi(Request $request)
    {
        $request->validate([
            'nisn' => 'required|exists:siswas,nisn',
            'nama_konfirmasi' => 'required|string'
        ]);

        $siswa = Siswa::where('nisn', $request->nisn)->first();

        $nama_input = strtolower(trim($request->nama_konfirmasi));
        $cocok = collect([
            $siswa->nama_ayah,
            $siswa->nama_ibu,
            $siswa->wali
        ])->filter()->map(fn($n) => strtolower(trim($n)));

        if ($cocok->contains($nama_input)) {
            return response()->json([
                'status' => 'success',
                'no_hp' => $siswa->no_hp
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Nama tidak cocok dengan data kami.'
        ]);
    }

    public function update(Request $request, $nisn)
    {
        $request->validate([
            'nisn' => 'required|exists:siswas,nisn',
            'no_hp' => 'required|string',
        ]);

        $siswa = Siswa::where('nisn', $nisn)->firstOrFail();
        $siswa->update([
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('pengisian.index')->with('success','Data berhasil di update');
    }
}
