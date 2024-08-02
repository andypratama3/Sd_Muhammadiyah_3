<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $gallerys = Gallery::select('name','foto')->orderBy('created_at','desc')->paginate(8);
        return view('gallery', compact('gallerys'));
    }
}
