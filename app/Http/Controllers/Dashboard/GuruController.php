<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Guru;
use App\Models\Karyawan;
use App\Models\Pelajaran;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\GuruData;
use App\Actions\Dashboard\Guru\GuruAction;
use App\Actions\Dashboard\Guru\DeleteGuruAction;

class GuruController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role: guru');
    // }
    public function index()
    {
        $limit = 15;
        $gurus = Guru::select(['name','description','lulusan','foto','slug'])->paginate($limit);
        $count = $gurus->count();
        $no = $limit * ($gurus->currentPage() - 1);
        return view('dashboard.guru.index', compact('gurus','count','no'));
    }
    public function create()
    {
        $pelajarans = Pelajaran::all();
        $karyawans = Karyawan::select(['id','name','slug'])->get();
        return view('dashboard.guru.create', compact('pelajarans','karyawans'));
    }
    public function store(GuruData $GuruData, GuruAction $guruAction)
    {
        /*
            todo check karyawan at database with karyawan_id
        */
        $karyawan = Guru::where('karyawan_id',$GuruData->karyawan_id)->first();

        if (!$karyawan) {
            $guruAction->execute($GuruData);
            return redirect()->route('dashboard.datasekolah.guru.index')->with('success', 'Berhasil Menambahkan Guru!');
        } else {
            return redirect()->route('dashboard.datasekolah.guru.index')->with('error', 'Gagal Guru Telah Ada!');
        }
    }



    public function show(Guru $guru)
    {
      return view('dashboard.guru.show', compact('guru'));
    }
    public function edit($slug)
    {
        $guru = Guru::with('karyawan')->where('slug',$slug)->firstOrFail();
        $karyawans = Karyawan::select(['id','name','slug'])->get();

        $pelajarans = Pelajaran::all();
        return view('dashboard.guru.edit', compact('guru','pelajarans', 'karyawans'));
    }
    public function update(GuruData $GuruData, GuruAction $guruAction)
    {
        $guruAction->execute($GuruData);
        dd($GuruData);
        return redirect()->route('dashboard.datasekolah.guru.index')->with('success','Berhasil Update Guru!');
    }
    public function destroy(DeleteGuruAction $DeleteGuruAction, $slug)
    {
        $DeleteGuruAction->execute($slug);
        return redirect()->route('dashboard.datasekolah.guru.index')->with('success','Berhasil Hapus Guru!');
    }

}
