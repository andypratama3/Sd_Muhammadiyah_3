<?php

namespace App\Http\Controllers\Dashboard;

use App\Charts\ArtikelView;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function __invoke(ArtikelView $ArtikelChart)
    {
        return view('dashboard.index', ['ArtikelChart' => $ArtikelChart->build()]);
    }
}
