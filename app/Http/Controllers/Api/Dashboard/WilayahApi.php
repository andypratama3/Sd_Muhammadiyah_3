<?php

namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;


class WilayahApi extends Controller
{
    public function provinsi()
    {
        $provinsi = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json");
        return $provinsi;
    }
    // public function kabupatenkota($provinsi_id)
    // {
    //     $url = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/`{$provinsi_id}`.json")->json();

    //     dd($url);
    //     // if($url){
    //     //     return response()->json(['data' => $url, 'success', 'Data Wilayah Sukses Di Ambil']);
    //     // }else{
    //     //     return response()->json(['error', 'Data Wilayah Tidak Ada']);
    //     // }
    // }
}
