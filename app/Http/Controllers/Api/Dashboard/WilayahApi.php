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
    public function kelurahan(Request $request)
    {
        $district_id = $request->district_id;
        $url = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/$district_id.json")->json();
        if($url){
            return response()->json(['data' => $url, 'success', 'Data Kecamatan Sukses Di Ambil']);
        }else{
            return response()->json(['error', 'Data Wilayah Tidak Ada']);
        }
    }
    /*
        ! take Function for get Data Wilayah
    */
    public function getProvinsi(Request $request)
    {
        /*
          ! take Request data From jquery
        */  
        $provinsi_id = $request->provinsi_id;
        $kabupaten_id = $request->kabupaten_id;
        $kecamatan_id = $request->kecamatan_id;

        $response_provinsi = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json");

        if ($response_provinsi->successful()) {
            /*
                ! take provinsi data from Api
             */
            $provinsi = $response_provinsi->json();
            $provinsi_take = collect($provinsi)->where('id', $provinsi_id)->first();

             /*
                ! take kabupaten data from Api
             */
            $response_kabupaten = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/$provinsi_id.json");
            $kabupaten = $response_kabupaten->json();
            $kabupaten_take = collect($kabupaten)->first();

             /*
                ! take kecamatan data from Api
             */
            $response_kecamatan = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/$kabupaten_id.json");
            $kecamatan = $response_kecamatan->json();
            $kecamatan_take = collect($kecamatan)->first();

             /*
                ! take kelurahan data from Api
             */
            $response_kelurahan = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/$kecamatan_id.json");
            $kelurahan = $response_kelurahan->json();
            $kelurahan_take = collect($kelurahan)->first();

            return response()->json([
                'success' => true,
                'message' => 'Data API Sukses Di Ambil',
                'provinsi' => $provinsi_take,
                'kabupaten' => $kabupaten_take,
                'kecamatan' => $kecamatan_take,
                'kelurahan' => $kelurahan_take,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data from the API',
            ]);
        }
    }

    // public function getKabupaten(Request $request)
    // {
    //     $provinsi_id = $request->provinsi_id;
    //     $kabupaten_id = $request->kabupaten_id;
    //     $response = Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/$provinsi_id.json");
    //     if ($response->successful()) {
    //         $kabupaten = $response->json();
    //         //use where in array method
    //         $kabupaten_take = collect($kabupaten)->where('regency_id', $kabupaten_id)->first();
    //         return response()->json(['response' => $kabupaten_take, 'success', 'Data kabupaten Sukses Di Ambil']);
    //     } else {
    //         // Handle the case where the request was not successful
    //         dd('Failed to retrieve data from the API');
    //     }
    // }

}
