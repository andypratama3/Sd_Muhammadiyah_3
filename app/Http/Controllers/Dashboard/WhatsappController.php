<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class WhatsappController extends Controller
{
    public function index()
    {
        return view('dashboard.whatsaap.index');
    }

    public function data_table(Request $request)
    {
        $data = DB::table('whatsapp_incoming_messages')->orderBy('created_at', 'asc')->get();

        return DataTables::of($data)
            ->addColumn('action', function ($row) {
                 return '
                    <button type="button" data-url="'.route('dashboard.monitoring.whatsapp.show', $row->id).'" class="btn btn-sm btn-warning btn-show-data" ><i class="fa fa-eye"></i></button>
                ';
            })
            ->editColumn('type', function ($row) {
                return ucfirst($row->type);
            })
            ->editColumn('profile_name', function ($row) {
                return ucwords($row->profile_name);
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }


    public function show($id)
    {
        $message = DB::table('whatsapp_incoming_messages')->where('id', $id)->first();


        if(!$message) {
            return response()->json(['status' => 'error', 'message' => 'Message not found']);
        }

        return response()->json(['status' => 'success', 'data' => $message]);

    }
}
