<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SiswaApi extends Controller
{
    public function siswa()
    {
        $siswas = Siswa::select([
        'name',
        'nisn',
        'jk',
        'tmpt_lahir',
        'tgl_lahir',
        'nik',
        'agama',
        'rt',
        'rw',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'nama_jalan',
        'jenis_tinggal',
        'no_hp',
        'beasiswa',
        'foto',
        'slug'])->orderBy('name', 'asc');
        // give response to route use
        $json_data = json_encode($siswas, true);
        // return response()->json($json_data, 200);
        return response()->json([
            'success' => true,
            'message' => 'Data Siswa Sukses Di Ambil',
            'siswas' => $json_data,
        ]);
    }
}
