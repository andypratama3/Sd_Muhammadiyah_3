<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attendances;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AttendancesController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::orderBy('name', 'asc')->get();

        $siswas = Siswa::orderBy('name', 'asc')->limit(15);

        $attendances = Attendances::orderBy('tanggal', 'asc')->get()->keyBy('siswa_id');
        // if($request->kelas_id){
        //     $siswas = $siswas->whereHas('kelas', function($q) use ($request){
        //         $q->where('kelas_id', $request->kelas_id);
        //     });
        // }

        // if($request->category_kelas){
        //     $siswas = $siswas->whereHas('kelas', function($q) use ($request){
        //         $q->where('category_kelas', $request->category_kelas);
        //     });
        // }

        // if($request->tanggal) {
        //     $siswas = $siswas->whereHas('attendances', function($q) use ($request){
        //         $q->where('tanggal', $request->tanggal);
        //     });
        // }

        // if($request->nama) {
        //     $siswas = $siswas->where('name', 'like', '%'.$request->nama.'%');
        // }

        // return DataTables::of($siswas)
        // ->addColumn('siswa_name', function ($row) {
        //     return $row->name ?? 'Tidak Ada Siswa';
        // })
        // ->addColumn('kelas_name', function ($row) {
        //     $kelas_name = $row->kelas->pluck('name')->implode(', ');
        //     $category_kelas = $row->kelas->first()->pivot->category_kelas ?? '';
        //     return $kelas_name . ' - ' . $category_kelas;
        // })
        // ->addColumn('status_masuk', function ($row) use ($attendances) {

        //     return $attendances[$row->id]->statsu ?? '';
        // })
        // ->addColumn('jam_keluar', function ($row) use ($attendances) {
        //     return $attendances[$row->id]->jam_keluar ?? '';
        // })
        // ->addColumn('action', function($row){

        // })
        // ->addIndexColumn()
        // ->make(true);

        return view('dashboard.attendances.index', compact('kelas'));
    }

    public function data_table() {}

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|string',
            'kelas_id' => 'required|string',
            'tanggal' => 'required|string',
            'status' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $carbonNow = \Carbon::now()->format('Y-m-d');

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
            return response()->json(['status' => 'error', 'message' => 'Data Tidak Ditemukan']);
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
}
