<?php

namespace App\Http\Controllers\Dashboard;


use App\Models\Role;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\KaryawanData;
use App\Actions\Dashboard\Karyawan\KaryawanStore;
use App\Actions\Dashboard\Karyawan\ActionKaryawan;
use App\Actions\Dashboard\Karyawan\karyawanDelete;
use App\Actions\Dashboard\Karyawan\KaryawanUpdate;
use App\Http\Requests\Dashboard\Karyawan\KaryawanRequest;

class KaryawanController extends Controller
{
    public function __contstruct()
    {
        $this->middleware('permission:view-admin', ['only' => ['index']]);
        $this->middleware('permission:create-admin', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-admin', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-admin', ['only' => ['delete']]);
    }

    public function index()
    {
        $limit = 15;
        $karyawans = Karyawan::select(['id','name','sex','phone','slug','user_id'])->where('slug', '!=', 'superadmin-xxxx')->with('user:id,email')->orderBy('name')->paginate($limit);
        $count = $karyawans->count();
        $no = $limit * ($karyawans->currentPage() - 1);
        $roles = Role::where('slug', '!=', 'superadmin')->get();
        return view('dashboard.pengaturan.karyawan.index', compact(
            'karyawans',
            'count',
            'no',
            'roles'
        ));
    }
    public function create()
    {
        $roles = Role::where('slug', '!=', 'superadmin')->orderBy('name')->get();
        $users = User::all();
        return view('dashboard.pengaturan.karyawan.create', compact('roles','users'));
    }
    public function store(KaryawanData $karyawanData, ActionKaryawan $ActionKaryawan)
    {
        $ActionKaryawan->execute($karyawanData);
        return redirect()->route('dashboard.pengaturan.karyawan.index')->with('success','Berhasil Menambahkan Karyawan!');
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
    public function update(KaryawanData $karyawanData, ActionKaryawan $ActionKaryawan)
    {
        $ActionKaryawan->execute($karyawanData);
        return redirect()->route('dashboard.pengaturan.karyawan.index')->with('success','Kayran Berhasil Di Update');
    }
    public function getEmailUser(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if($user){
            return response()->json(['status','success' => 'Email Telah Ada']);
        }else{
            return response()->json(['status','error' => 'Email Bisa Di Gunakan']);

        }
    }
    public function destroy(Karyawan $karyawan, karyawanDelete $karyawanDelete)
    {
        try {
            $karyawanDelete->execute($karyawan);
        } catch (\Throwable $th) {
            abort(404, $th);
        }
        return redirect()->route('dashboard.pengaturan.karyawan.index')->with('success','Berhasil Menghapus Karyawan!');

    }

}
