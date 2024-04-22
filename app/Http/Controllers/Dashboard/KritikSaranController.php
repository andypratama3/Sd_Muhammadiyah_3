<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Actions\Dashboard\KritikSaran\KritikSaranActionDelete;
use Illuminate\Http\Request;
use App\Models\KritikSaran;

class KritikSaranController extends Controller
{
    public function index()
    {
        $limit = 15;
        $kritiksarans = KritikSaran::orderBy('created_at', 'asc')->paginate($limit);
        $no = $limit * ($kritiksarans->currentPage() - 1);
        return view('dashboard.kritiksaran.index', compact('no','kritiksarans'));
    }
    public function show($kritiksaran)
    {
        $kritik = KritikSaran::where('slug', $kritiksaran)->firstOrFail();

        return view('dashboard.kritiksaran.show', compact('kritik'));
    }
    public function destroy(KritikSaranActionDelete $KritikSaranActionDelete, $slug)
    {
        $KritikSaranActionDelete->execute($slug);
        return redirect()->route('dashboard.kritik.saran.index')->with('success','Berhasil Hapus Kritik dan Saran!');
    }

}
