<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Achivement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\DataTransferObjects\AchivementData;
use App\Actions\Dashboard\Achivement\AchivementAction;

class AchivementController extends Controller
{
    public function index()
    {
        return view('dashboard.achivement.index');
    }

    public function data_table()
    {
        $achivements = Achivement::orderBy('order', 'asc');

        return DataTables::of($achivements)
            ->addColumn('foto', function ($row){
                return '<img src="' . asset('storage/img/achivement/' . $row->foto) . '" width="100" height="100" class="img-thumbnail" alt="Foto Achivement">';
            })
            ->addColumn('options', function ($achivement) {
                $actions = '';

                $actions .= '<a href="' . route('dashboard.datasekolah.achivement.edit', $achivement->slug) . '" class="btn me-2 btn-primary btn-sm"><i class="fa fa-edit"></i></a>';

                $actions .= '<button data-id="' . $achivement['slug'] . '" class="btn me-2 btn-danger btn-sm" id="btn-delete"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->addIndexColumn()
            ->rawColumns(['foto','options'])
            ->make(true);
    }

    public function create()
    {
        return view('dashboard.achivement.create');
    }

    public function store(AchivementData $achivementData, AchivementAction $achivementAction)
    {
        $achivementAction->execute($achivementData);

        return redirect()->route('dashboard.datasekolah.achivement.index')->with('success', 'Data Berhasil Ditambahkan');
    }

    public function edit(Achivement $achivement)
    {
        return view('dashboard.achivement.edit', compact('achivement'));
    }

    public function update(AchivementData $achivementData, AchivementAction $achivementAction)
    {
        $achivementAction->execute($achivementData);

        return redirect()->route('dashboard.datasekolah.achivement.index')->with('success', 'Data Berhasil Diubah');
    }

    public function destroy(Achivement $achivement)
    {
        $action = $achivement->delete();

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
