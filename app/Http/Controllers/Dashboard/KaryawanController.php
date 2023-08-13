<?php

namespace App\Http\Controllers\Dashboard;


use App\Models\Role;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Dashboard\Karyawan\KaryawanStore;
use App\Actions\Dashboard\Karyawan\KaryawanUpdate;
use App\Http\Requests\Dashboard\Karyawan\KaryawanRequest;

class KaryawanController extends Controller
{
    public function __contstruct()
    {
        $this->middleware('permission:view-karyawan', ['only' => ['index']]);
        $this->middleware('permission:create-karyawan', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-karyawan', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-karyawan', ['only' => ['delete']]);
    }

    public function index()
    {
        $limit = 15;
        $karyawans = Karyawan::select(['id','name','sex','phone','slug','user_id'])->where('slug', '!=', 'superadmin-xxxx')->with('user:id,email')->orderBy('name')->paginate($limit);
        $count = $karyawans->count();
        $no = $limit * ($karyawans->currentPage() - 1);

        return view('dashboard.pengaturan.karyawan.index', compact(
            'karyawans',
            'count',
            'no'
        ));
    }
    public function create()
    {
        $roles = Role::where('slug', '!=', 'superadmin')->orderBy('name')->get();
        $users = User::all();
        return view('dashboard.pengaturan.karyawan.create', compact('roles','users'));
    }
    public function store(KaryawanRequest $request, KaryawanStore $karyawanStore)
    {
        $karyawanStore->execute($request);
        return redirect()->route('dashboard.pengaturan.karyawan.index')->with('Berhasil Menambahkan Karyawan!');
    }
    public function show(Karyawan $karyawan)
    {
        return view('dashboard.pengaturan.karyawan.show', compact('karyawan'));
    }
    public function edit(Karyawan $karyawan)
    {
        $roles = Role::where('slug', '!=', 'superadmin')->get();
        return view('dashboard.pengaturan.karyawan.edit', compact('karyawan', 'roles'));
    }
    public function update(KaryawanUpdate $karyawanUpdate, KaryawanData $karyawanData)
    {
        $karyawanUpdate->execute($karyawanData);
        return redirect()->route('dashboard.pengaturan.karyawan.index')->with('succes', 'Kayran Berhasil Di Update');
    }
    public function destroy(Karyawan $karyawan)
    {
        try {
            $karyawanDelete->execute($karyawan);
        } catch (\Throwable $th) {
            abort(404, $th);
        }
        return redirect()->route('dashboard.pengaturan.karyawan.index')->with('Berhasil Menghapus Karyawan!');

    }

}
