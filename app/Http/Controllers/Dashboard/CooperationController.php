<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Cooperation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\DataTransferObjects\CooperationData;
use App\Actions\Dashboard\Cooperation\CooperationAction;

class CooperationController extends Controller
{
    public function index()
    {
        return view('dashboard.cooperation.index');
    }

    public function data_table(Request $request)
    {
        $cooperations = Cooperation::orderBy('created_at', 'desc');

        return DataTables::of($cooperations)
            ->addColumn('foto', function ($row){
                return '<img src="' . asset('storage/img/cooperation/' . $row->foto) . '" width="100" height="100" class="img-thumbnail" alt="Foto Berita">';
            })
            ->addColumn('options', function ($cooperation) {
                $actions = '';

                $actions .= '<a href="' . route('dashboard.datasekolah.cooperation.edit', $cooperation->slug) . '" class="btn me-2 btn-primary btn-sm"><i class="fa fa-edit"></i></a>';

                $actions .= '<button data-id="' . $cooperation['slug'] . '" class="btn me-2 btn-danger btn-sm" id="btn-delete"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['foto','options'])
            ->addIndexColumn()
            ->make(true);
    }

    public function create()
    {
        return view('dashboard.cooperation.create');
    }

    public function store(CooperationData $cooperationData, CooperationAction $cooperationAction)
    {
        $cooperationAction->execute($cooperationData);

        return redirect()->route('dashboard.datasekolah.cooperation.index')->with('success', 'Data Berhasil Ditambahkan');
    }

    public function edit(Cooperation $cooperation)
    {
        // $cooperation = Cooperation::findOrFail($id);

        return view('dashboard.cooperation.edit', compact('cooperation'));
    }

    public function update(CooperationData $cooperationData, CooperationAction $cooperationAction, $id)
    {
        $cooperationAction->execute($cooperationData, $id);

        return redirect()->route('dashboard.datasekolah.cooperation.index')->with('success', 'Data Berhasil Diubah');
    }

    public function destroy(Cooperation $cooperation)
    {
        // $cooperation = Cooperation::find($id);
        $action = $cooperation->delete();
        if($action){
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil Menghapus Data'
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Menghapus Data'
            ]);
        }
    }


}
