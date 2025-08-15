<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Attendances;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class AttendancesController extends Controller
{
    public function index(Request $request)
    {
        // Ambil daftar kelas dan urutkan berdasarkan angka setelah kata 'Kelas'
        $kelas = Kelas::where('name', '!=', 'Lulus')
            ->orderByRaw('CAST(SUBSTRING(name, 7) AS UNSIGNED) ASC')
            ->get();

        // Ambil siswa dengan relasi kelas
        $siswas = Siswa::with(['kelas' => function ($query) {
                $query->where('name', '!=', 'Lulus')
                    ->orderByRaw('CAST(SUBSTRING(name, 7) AS UNSIGNED) ASC');
            }])
            ->whereHas('kelas', function ($query) {
                $query->where('name', '!=', 'Lulus');
            })
            ->when($request->kelas_id || ($request->category_kelas && $request->category_kelas !== 'null'), function ($query) use ($request) {
                $query->whereHas('kelas', function ($q) use ($request) {
                    if ($request->kelas_id) {
                        $q->where('id', $request->kelas_id);
                    }
                    if ($request->category_kelas && $request->category_kelas !== 'null') {
                        $q->where('siswa_kelas.category_kelas', $request->category_kelas);
                    }
                });
            })
            ->when($request->filled('nama'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->nama . '%');
            })
            ->get();

        // Sortir siswa berdasarkan angka kelas dari relasi
        $siswas = $siswas->sortBy(function ($siswa) {
            // Ambil kelas pertama dari siswa (karena bisa ada banyak)
            $kelasName = $siswa->kelas->first()->name ?? 'Kelas 99';
            preg_match('/Kelas\s(\d+)/', $kelasName, $matches);
            return isset($matches[1]) ? (int)$matches[1] : 99;
        })->values();

        // Ambil tanggal dari request atau default hari ini
        $tanggal = $request->input('tanggal') ?? date('Y-m-d');

        // Ambil data absensi per siswa untuk tanggal tersebut
        $attendances = Attendances::whereDate('tanggal', $tanggal)->get()->keyBy('siswa_id');

        return view('dashboard.attendances.index', compact('siswas', 'kelas', 'attendances', 'tanggal'));
    }

    public function data_table()
    {

    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|string',
            'kelas_id' => 'required|string',
            'tanggal' => 'required|string',
            'kategori_kelas' => 'required|string',
            'status' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $carbonNow = Carbon::now()->format('Y-m-d');

        // checked data siswa
        $siswa = Siswa::where('id', $request->siswa_id)->first();

        if (! $siswa) {
            return response()->json(['status' => 'error', 'message' => 'Data Siswa Tidak Ditemukan']);
        }

        // checked attendance
        $attendance = Attendances::where('siswa_id', $request->siswa_id)->where('tanggal', $carbonNow)->first();

        if ($attendance === null) {
            $attendance = new Attendances;
            $attendance->siswa_id = $request->siswa_id;
            $attendance->kelas_id = $request->kelas_id;
            $attendance->kategori_kelas = $request->kategori_kelas;
            $attendance->tanggal = $carbonNow;
            $attendance->status = $request->status;
            $attendance->keterangan = $request->keterangan;
            $attendance->save();
        } else {
            $attendance->status = $request->status;
            $attendance->keterangan = $request->keterangan;
            $attendance->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
    }

    public function edit($id)
    {
        $attendance = Attendances::where('id', $id)->first();
        if (! $attendance) {
            return response()->json(['status' => 'wrror', 'message' => 'Data Tidak Ditemukan']);
        }

        return response()->json(['status' => 'success', 'data' => $attendance]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $attendance = Attendances::where('id', $id)->first();

        if (! $attendance) {
            return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan']);
        }

        $attendance->status = $request->status;
        $attendance->keterangan = $request->keterangan;
        $attendance->save();

        return response()->json(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
    }

    public function export(Request $request)
    {
        $request->validate([
            'date' => 'required',
        ]);

        return Excel::download(new AttendancesExport, "absensi_$request->date.xlsx");
    }
}
