<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Artikel;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\DataTransferObjects\ArtikelData;
use Yajra\DataTables\Facades\DataTables;
use App\Actions\Dashboard\Artikel\ArtikelAction;
use App\Actions\Dashboard\Artikel\ArtikelDeleteAction;

class ArtikelController extends Controller
{
    public function index()
    {

        return view('dashboard.artikel.index');
    }
    public function data_table()
    {

        $query = Artikel::with('categorys')->select('id','name','image','jumlah_klik','artikel','slug','status','user_id')->orderBy('created_at','desc');
        if(Auth::user()->hasRole('user'))
        {
            $query = $query->where('user_id',Auth::user()->id);
        }
        return DataTables::of($query)
                ->addColumn('kategori.name', function ($artikel) {
                    $categoryNames = $artikel->categorys->pluck('name')->implode(', ') ?? 'Tidak Ada Kategori';

                    return $categoryNames;
                })
                ->addColumn('options', function ($row){
                    return '
                    <a href="' . route('dashboard.news.artikel.show', $row->slug) . '" class="btn btn-sm me-1 btn-warning"><i class="fa fa-eye"></i></a>
                    <a href="' . route('dashboard.news.artikel.edit', $row->slug) . '" class="btn btn-sm me-1 btn-primary"><i class="fa fa-pen"></i></a>
                    <button data-id="' . $row['slug'] . '" class="btn btn-sm btn-danger me-1" id="btn-delete"><i class="fa fa-trash"></i></button>
                ';
                })
                ->addColumn('status', function ($row) {
                    $userRoles = Auth::user()->roles()->pluck('name')->toArray();

                    // Cek apakah user memiliki peran 'superadmin'
                    if (in_array('superadmin', $userRoles)) {
                        $btnClass = $row->status == 'publish' ? 'warning' : 'success';
                        $nextStatus = $row->status == 'pending' ? 'publish' : 'pending';
                        $icon = $row->status == 'pending' ? 'check' : 'clock';

                        return '<a href="' . route('dashboard.news.artikel.status', e($row->slug)) . '"
                                    class="btn btn-sm btn-' . $btnClass . '">
                                    <i class="fa fa-' . $icon . '"></i> ' . ucfirst($nextStatus) . '
                                </a>';
                    } else {
                        if ($row->status == 'publish') {
                            return '<span class="badge bg-success badge-sm"><i class="bx bx-check"></i> Publish</span>';
                        } else {
                            return '<span class="badge bg-danger badge-sm"><i class="bx bx-time-five"></i> Pending</span>';
                        }
                    }
                })

                ->rawColumns(['options','status'])
                ->addIndexColumn()
                ->make(true);

    }
    public function create()
    {
        $categorys = Category::select('id','name','slug')->get();
        return view('dashboard.artikel.create', compact('categorys'));
    }
    public function store(ArtikelData $artikelData, ArtikelAction $artikelAction)
    {
        $artikelAction->execute($artikelData);
        activity()->log('Menambahkan Artikel');
        return redirect()->route('dashboard.news.artikel.index')->with('success','Berhasil Menambahkan Artikel');
    }
    public function show(Artikel $artikel)
    {

        return view('dashboard.artikel.show', compact('artikel'));
    }
    public function edit(Artikel $artikel)
    {
        $categorys = Category::select('id','name','slug')->get();
        return view('dashboard.artikel.edit', compact('artikel','categorys'));
    }
    public function update(ArtikelData $artikelData, ArtikelAction $artikelAction, $slug)
    {
        $artikelAction->execute($artikelData, $slug);
        return redirect()->route('dashboard.news.artikel.index')->with('success','Berhasil Update Artikel');

    }
    public function destroy(ArtikelDeleteAction $artikelActionDelete,$slug)
    {
        if($artikelActionDelete)
        {
            $artikelActionDelete->execute($slug);
            return response()->json(['status' => 'success', 'message' => 'Berhasil Menghapus Artikel']);
            // toaster()->sucess('Berhasil Menghapus Artikel');
        }else{
            return response()->json(['status' => 'error', 'message' => 'Gagal Menghapus Artikel']);
        }
    }

    public function status($slug)
    {
        $artikel = Artikel::where('slug', $slug)->first();
        if($artikel->status == 'pending')
        {
            $artikel->status = 'publish';
        }else{
            $artikel->status = 'pending';
        }

        

        $artikel->update();

        return redirect()->back()->with('success','Berhasil Mengubah Status Publish Artikel');
    }
}
