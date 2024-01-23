<?php

namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class WilayahApi extends Controller
{
    public function provinsi()
    {
        $provinsi = Http::get('https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json')->json();
        dd($provinsi);
    }
}
