<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $karyawans = Karyawan::select([ 'name','sex','birth_date','phone','slug','user_id'])->where('slug', '!=', 'superadmin-xxxx')->with('user:id,email')->orderBy('name')->paginate($limit);
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
        return view('dashbord.pengaturan.karyawan.create');
    }

}
