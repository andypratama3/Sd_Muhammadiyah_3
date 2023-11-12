<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\Ekstrakulikuler;
use App\Models\Esktrakurikuler;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\EsktrakurikulerData;
use App\Actions\Dashboard\Esktrakurikuler\ActionEkstrakurikuler;

class EkstrakulikulerController extends Controller
{
    public function index()
    {
        $no = 0;
        $ekstrakurikuler = Esktrakurikuler::select(['id','name','desc','foto','slug'])->get();
        return view('dashboard.esktrakurikuler.index', compact('no','ekstrakurikuler'));
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
}
