<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\Dashboard\WilayahApi;

class SiswaController extends Controller
{
    // protected $wilayahApi;
    // public function __construct(WilayahApi $wilayahApi)
    // {
    //     $this->getprovinsi = $wilayahApi;
    // }
    public function index()
    {
        return view('dashboard.data.siswa.index');
    }
    public function create()
    {
        // $result_provinsi = $this->getprovinsi->provinsi();
        $result_provinsi = Http::get(route('provinsi.api'))->json();
        $kelass = Kelas::all();
        return view('dashboard.data.siswa.create', compact('kelass','result_provinsi'));
    }

}
