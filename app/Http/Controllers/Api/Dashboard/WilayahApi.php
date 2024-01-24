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
    public function kabupaten(Request $request)
    {
        $provinsi_id = $request->provinsi_id;
        $url = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/$provinsi_id.json")->json();
        if($url){
            return response()->json(['data' => $url, 'success', 'Data Wilayah Sukses Di Ambil']);
        }else{
            return response()->json(['error', 'Data Wilayah Tidak Ada']);
        }
    }
    public function kecamatan(Request $request)
    {
        $regency_id = $request->regency_id;
        $url = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/$regency_id.json")->json();
        if($url){
            return response()->json(['data' => $url, 'success', 'Data Kecamatan Sukses Di Ambil']);
        }else{
            return response()->json(['error', 'Data Wilayah Tidak Ada']);
        }
    }
}
