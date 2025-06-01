<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Spmb;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class SpmbController extends Controller
{
    public function index()
    {
        return view('dashboard.spmb.index');
    }

    public function data_table(Request $request)
    {
        $spmb = Spmb::whereYear('created_at', date('Y'))->orderBy('created_at', 'desc');

        return DataTables::of($spmb)
            ->addColumn('nomor_urut', function ($row) {
                $nomor_urut =  sprintf('%03d', $row->nomor_urut);
                return $nomor_urut;
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('dashboard.spmb.show', $row->id) . '" class="btn btn-sm btn-warning"><i class="fa fa-eye"></i></a>
                    <a href="' . route('dashboard.spmb.edit', $row->id) . '" class="btn btn-sm btn-primary"><i class="fa fa-pen"></i></a>
                    <button data-id="' . $row['id'] . '" class="btn btn-sm btn-danger" id="btn-delete"><i class="fa fa-trash"></i></button>
                ';
            })
            ->addColumn('status', function ($row) {
                $status = $row->status_spmb;

                if ($status == 'pending') {
                    return '<span class="badge bg-warning">BELUM DIKONFIRMASI</span>';
                } else {
                    return '<span class="badge bg-success">DIKONFIRMASI</span>';
                }

            })
            ->rawColumns(['action','status'])
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

