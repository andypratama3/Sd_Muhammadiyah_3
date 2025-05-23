<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Spmb;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SpmbController extends Controller
{
    public function index()
    {
        return view('dashboard.spmb.index');
    }

    public function data_table(Request $request)
    {
        $spmb = Spmb::orderBy('created_at', 'desc');

        return DataTables::of($spmb)
            ->addColumn('action', function ($row) {

            })
            ->addIndexColumn()
            ->make(true);
    }

    // public
}
