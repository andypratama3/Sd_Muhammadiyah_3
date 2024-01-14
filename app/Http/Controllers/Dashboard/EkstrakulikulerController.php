<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\Ekstrakulikuler;
use App\Models\Esktrakurikuler;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\EsktrakurikulerData;
use App\Actions\Dashboard\Esktrakurikuler\ActionEkstrakurikuler;
use App\Actions\Dashboard\Esktrakurikuler\DeleteEsktrakurikuler;

class EkstrakulikulerController extends Controller
{
    public function index()
    {
        $limit = 15;
        $ekstrakurikuler = Esktrakurikuler::select(['id','name','desc','foto','slug'])->orderBy('created_at', 'asc')->paginate($limit);
        $count = Esktrakurikuler::count();
        $no = $limit * ($ekstrakurikuler->currentPage() - 1);
        return view('dashboard.esktrakurikuler.index', compact('no','ekstrakurikuler','count'));
    }
    public function create()
    {
        return view('dashboard.esktrakurikuler.create');
    }
    public function store(EsktrakurikulerData $ekstrakurikulerData, ActionEkstrakurikuler $ActionEkstrakurikuler)
    {
        $ActionEkstrakurikuler->execute($ekstrakurikulerData);
        return redirect()->route('dashboard.ekstrakurikuler.index')->with('success','Berhasil Menambahkan Esktrakurikuller');
    }
    public function show(Esktrakurikuler $ekstrakurikuler)
    {
        return view('dashboard.esktrakurikuler.show', compact('ekstrakurikuler'));
    }
    public function edit(Esktrakurikuler $ekstrakurikuler)
    {
        return view('dashboard.esktrakurikuler.edit', compact('ekstrakurikuler'));
    }
    public function update(EsktrakurikulerData $ekstrakurikulerData, ActionEkstrakurikuler $ActionEkstrakurikuler)
    {
        $ActionEkstrakurikuler->execute($ekstrakurikulerData);
        return redirect()->route('dashboard.ekstrakurikuler.index')->with('success','Berhasil Mengubah Esktrakurikuller');
    }
    public function destroy(DeleteEsktrakurikuler $DeleteEsktrakurikuler, $slug)
    {
        $DeleteEsktrakurikuler->execute($slug);
        return redirect()->route('dashboard.ekstrakurikuler.index')->with('success', 'Esktrakurikuller Berhasil Di Hapus!');
    }

}
