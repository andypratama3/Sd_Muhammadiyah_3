<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\DataTransferObjects\SiswaData;
use App\Actions\Dashboard\Siswa\SiswaAction;
use App\Http\Controllers\Api\Dashboard\WilayahApi;

class SiswaController extends Controller
{
    protected $wilayahApi;
    public function __construct(WilayahApi $wilayahApi)
    {
        $this->getprovinsi = $wilayahApi;
        $this->getKelurahan = $wilayahApi;
    }
    public function index()
    {
        return view('dashboard.data.siswa.index');
    }
    public function create()
    {
        $result_provinsi = $this->getprovinsi->provinsi()->json();
        // $result_provinsi = Http::get(route('provinsi.api'))->json();
        $kelass = Kelas::all();
        return view('dashboard.data.siswa.create', compact('kelass','result_provinsi'));
    }
    public function store(SiswaData $siswaData, SiswaAction $siswaAction)
    {
        $siswaAction->execute($siswaData);
        return redirect()->route('dashboard.datamaster.siswa.index')->with('success','Berhasil Menambahkan Siswa');
    }
    public function show(Siswa $siswa)
    {
        return view('dashboard.data.siswa.show', compact('siswa'));
    }
    public function edit(Siswa $siswa)
    {
        return view('dashboard.data.siswa.edit',compact('siswa'));
    }
    public function update(SiswaData $siswaData, SiswaAction $siswaAction)
    {
        $siswaAction->execute($siswaData);
        return redirect()->route('dashboard.datamaster.siswa.index')->with('success','Berhasil Update Siswa');
    }
    public function destroy(Siswa $siswa)
    {
        //do here
    }
    public function checknik(Request $request)
    {
        $nik = $request->nik;
        $siswa = Siswa::where('nik', $nik)->first();
        if(empty($siswa)){
            return response()->json(['siswa' => $siswa, 'Nik Bisa Di gunakan']);
        }else {
            return response()->json(['error','Nik Telah Ada']);
        }
    }
    public function checknisn(Request $request)
    {
        $nisn = $request->nisn;
        $siswa = Siswa::where('nisn', $nisn)->first();
        if(empty($siswa)){
            return response()->json(['siswa' => $siswa, 'nisn Bisa Di gunakan']);
        }else {
            return response()->json(['error','nisn Telah Ada']);
        }
    }
}
