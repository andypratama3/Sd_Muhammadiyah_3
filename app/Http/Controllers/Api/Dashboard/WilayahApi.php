<?php

namespace App\Http\Controllers\Api\Dashboard;

use DB;
use App\Models\Siswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;


class WilayahApi extends Controller
{
    public function provinsi()
    {
        // $provinsi = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/provinces.json");
        $provinsi = DB::table('provinsi')->orderBy('name')->get();
        // dd($provinsi);
        return $provinsi;
    }
    public function kabupaten(Request $request)
    {
        // $url = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/regencies/$provinsi_id.json")->json();
        $provinsi_id = $request->provinsi_id;
        $url = DB::table('kabupaten')->where('province_id', $provinsi_id)->get();

        $url = json_decode(json_encode($url), true);

        if($url){
            return response()->json(['data' => $url, 'success', 'Data Wilayah Sukses Di Ambil']);
        }else{
            return response()->json(['error', 'Data Wilayah Tidak Ada']);
        }
    }
    public function kecamatan(Request $request)
    {
        // $url = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/districts/$regency_id.json")->json();
        $regency_id = $request->regency_id;
        $url = DB::table('kecamatan')->where('regency_id', $regency_id)->get();

        $url = json_decode(json_encode($url), true);

        if($url){
            return response()->json(['data' => $url, 'success', 'Data Kecamatan Sukses Di Ambil']);
        }else{
            return response()->json(['error', 'Data Wilayah Tidak Ada']);
        }
    }
    public function kelurahan(Request $request)
    {
        // $url = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/villages/$district_id.json")->json();
        $district_id = $request->district_id;

        $url = DB::table('kelurahan')->where('district_id', $district_id)->get();

        $url = json_decode(json_encode($url), true);

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
        $provinsi_id = $request->provinsi_id;
        $kabupaten_id = $request->kabupaten_id;
        $kecamatan_id = $request->kecamatan_id;
        $kelurahan_id = $request->kelurahan_id;

        // Ambil data dari database
        $provinsi = DB::table('provinsi')->where('province_id', $provinsi_id)->first();
        $kabupaten = DB::table('kabupaten')->where('regency_id', $kabupaten_id)->first();

        $kecamatan = DB::table('kecamatan')->where('district_id', $kecamatan_id)->first();
        $kelurahan = DB::table('kelurahan')->where('village_id', $kelurahan_id)->first();


        if (!$provinsi || !$kabupaten || !$kecamatan || !$kelurahan) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data wilayah berhasil diambil',
            'provinsi' => $provinsi,
            'kabupaten' => $kabupaten,
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan,
        ]);
    }

    // public function getProvinsi(Request $request)
    // {
    //     $provinsi_id = $request->provinsi_id;
    //     $kabupaten_id = $request->kabupaten_id;
    //     $kecamatan_id = $request->kecamatan_id;
    //     /*
    //       ! take Request data From jquery
    //     */
    //     // if($provinsi_id == null && $kabupaten_id == null && $kecamatan_id == null)
    //     // {
    //     //     $provinsi_id = $siswa->provinsi_id;
    //     //     $kabupaten_id = $siswa->kabupaten_id;
    //     //     $kecamatan_id = $siswa->kecamatan_id;
    //     // } else {
    //     //     $provinsi_id = $request->provinsi_id;
    //     //     $kabupaten_id = $request->kabupaten_id;
    //     //     $kecamatan_id = $request->kecamatan_id;
    //     // }
    //     // $response_provinsi = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/provinces.json");

    //     $response_provinsi = DB::table('provinsi')->orderBy('name')->get();

    //     if ($response_provinsi->successful()) {
    //         /*
    //             ! take provinsi data from Api
    //          */
    //         $provinsi = $response_provinsi->json();
    //         $provinsi_take = collect($provinsi)->where('id', $provinsi_id)->first();

    //          /*
    //             ! take kabupaten data from Api
    //          */
    //         $response_kabupaten = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/regencies/$provinsi_id.json");
    //         $kabupaten = $response_kabupaten->json();
    //         $kabupaten_take = collect($kabupaten)->first();

    //          /*
    //             ! take kecamatan data from Api
    //          */
    //         $response_kecamatan = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/districts/$kabupaten_id.json");
    //         $kecamatan = $response_kecamatan->json();
    //         $kecamatan_take = collect($kecamatan)->first();

    //          /*
    //             ! take kelurahan data from Api
    //          */
    //         $response_kelurahan = Http::get("https://andypratama3.github.io/api-wilayah-indonesia/api/villages/$kecamatan_id.json");
    //         $kelurahan = $response_kelurahan->json();
    //         $kelurahan_take = collect($kelurahan)->first();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data API Sukses Di Ambil',
    //             'provinsi' => $provinsi_take,
    //             'kabupaten' => $kabupaten_take,
    //             'kecamatan' => $kecamatan_take,
    //             'kelurahan' => $kelurahan_take,
    //         ]);
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to retrieve data from the API',
    //         ]);
    //     }
    // }

}
