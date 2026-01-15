<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jadwal;
use App\Models\Pelajaran;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Dashboard\Jadwal\JadwalAction;
use App\Actions\Dashboard\Jadwal\JadwalActionDelete;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = Jadwal::with('kelas_jadwal', 'jadwal_details')
            ->select('id', 'tahun_ajaran', 'jadwal', 'kelas_id', 'category_kelas', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.data.jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        $kelass = Kelas::select('id', 'name', 'category_kelas')->orderBy('name')->get();
        $pelajaran = Pelajaran::where(function ($q) {
            $q->where('name', 'NOT LIKE', 'Guru Kelas%')
            ->where('name', 'NOT LIKE', 'Shadow%');
        })
        ->orderBy('name')
        ->get();



        // filter when name Guru Kelas * not show in select


        $guru = Guru::orderBy('name', 'asc')->get();

        return view('dashboard.data.jadwal.create', compact('kelass', 'pelajaran', 'guru'));
    }

    public function store(Request $request, JadwalAction $jadwalAction)
    {
        // Validasi input
        $validated = $request->validate([
            'kelas' => 'required|exists:kelas,id',
            'category_kelas' => 'required|string|max:50',
            'tahun_ajaran' => 'required|string|regex:/^\d{4}\/\d{4}$/',
            'jadwal_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
            'jadwal' => 'required|array|min:1',
            'jadwal.*.hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jadwal.*.mulai' => 'required|date_format:H:i:s',
            'jadwal.*.selesai' => 'required|date_format:H:i:s|after:jadwal.*.mulai',
            'jadwal.*.pelajaran_id' => 'nullable|exists:pelajarans,id',
            'jadwal.*.guru_id' => 'nullable|exists:gurus,id',
            'jadwal.*.color' => 'nullable|string',
        ]);

        // Cek duplikasi jadwal
        $exists = Jadwal::where('kelas_id', $validated['kelas'])
            ->where('tahun_ajaran', $validated['tahun_ajaran'])
            ->where('category_kelas', $validated['category_kelas'])
            ->exists();

        if ($exists) {
            return redirect()->route('dashboard.datasekolah.jadwal.index')
                ->with('error', 'Jadwal untuk kelas, tahun ajaran, dan kategori ini sudah ada!');
        }

        try {
            $jadwalAction->execute($request, false);
            return redirect()->route('dashboard.datasekolah.jadwal.index')
                ->with('success', 'Jadwal berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $jadwal = Jadwal::with('jadwal_details.pelajarans', 'jadwal_details.guru', 'kelas_jadwal')
            ->findOrFail($id);

        return view('dashboard.data.jadwal.show', compact('jadwal'));
    }

    public function edit($id)
    {
        $jadwal = Jadwal::with('jadwal_details')->findOrFail($id);
        $kelass = Kelas::select('id', 'name', 'category_kelas')->orderBy('name')->get();
        $pelajaran = Pelajaran::orderBy('name', 'asc')->get();
        $guru = Guru::orderBy('name', 'asc')->get();

        return view('dashboard.data.jadwal.edit', compact('jadwal', 'kelass', 'pelajaran', 'guru'));
    }

    public function update(Request $request, $id, JadwalAction $jadwalAction)
    {
        $jadwal = Jadwal::findOrFail($id);

        // Validasi input
        $validated = $request->validate([
            'kelas' => 'required|exists:kelas,id',
            'category_kelas' => 'required|string|max:50',
            'tahun_ajaran' => 'required|string|regex:/^\d{4}\/\d{4}$/',
            'jadwal_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
            'jadwal' => 'required|array|min:1',
            'jadwal.*.hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jadwal.*.mulai' => 'required|date_format:H:i:s',
            'jadwal.*.selesai' => 'required|date_format:H:i:s|after:jadwal.*.mulai',
            'jadwal.*.pelajaran_id' => 'nullable|exists:pelajarans,id',
            'jadwal.*.guru_id' => 'nullable|exists:gurus,id',
            'jadwal.*.color' => 'nullable|string',
        ]);

        try {
            $jadwalAction->execute($request, true, $id);
            return redirect()->route('dashboard.datasekolah.jadwal.index')
                ->with('success', 'Jadwal berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $jadwal = Jadwal::findOrFail($id);
            $jadwal->jadwal_details()->delete();
            $jadwal->delete();

            return redirect()->route('dashboard.datasekolah.jadwal.index')
                ->with('success', 'Jadwal berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getCategoryKelas(Request $request)
    {
        $kelasId = $request->input('id');
        $kelas = Kelas::find($kelasId);

        if (!$kelas) {
            return response()->json(['error' => 'Kelas tidak ditemukan'], 404);
        }

        if ($kelas->name === 'Lulus') {
            $currentYear = date('Y');
            $years = range(2019, $currentYear);
            $categoryKelas = array_map(fn($year) => (string)$year, $years);
            return response()->json($categoryKelas);
        }

        $categoryKelas = json_decode($kelas->category_kelas, true) ?? [];
        sort($categoryKelas);

        return response()->json($categoryKelas);
    }
}
