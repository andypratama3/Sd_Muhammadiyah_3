<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UrlVisitorController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $datas = DB::table('url_visitor')
                ->groupBy('url')
                ->selectRaw('url, count(*) as count');

            return DataTables::of($datas)
                ->addColumn('url', function ($data) {
                    return $data->url;
                })
                ->addColumn('count', function ($data) {
                    return $data->count;
                })
                ->addIndexColumn()
                ->make(true);
        }

        return view('dashboard.url_visitor.index');
    }
}
