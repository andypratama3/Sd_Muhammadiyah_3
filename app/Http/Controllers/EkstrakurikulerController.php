<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Esktrakurikuler;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $ekstrakurikulers = Esktrakurikuler::select(['name','desc','foto','slug'])->get();
        return view('profil.esktrakurikuler.index', compact('ekstrakurikulers'));
    }
    public function show($name)
    {
        $ekstrakurikuler = Esktrakurikuler::where('name',$name)->firstOrFail();
        return view('profil.esktrakurikuler.show',compact('ekstrakurikuler'));
    }
}
