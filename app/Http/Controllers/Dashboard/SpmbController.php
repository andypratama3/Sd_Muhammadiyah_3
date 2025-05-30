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

    // public function

    public function edit(Spmb $spmb)
    {
        return view('dashboard.spmb.show', compact('spmb'));
    }

    public function destroy()
    {
        $spmb = Spmb::where('id', $id)->firstOrFail();

        $action = $spmb->delete();

        if(!$action) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Menghapus Data'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil Menghapus Data'
        ]);
    }
}

