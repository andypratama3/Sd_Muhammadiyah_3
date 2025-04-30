<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $gallerys = Gallery::select('name','foto','slug')->orderBy('created_at','desc')->paginate(8);
        return view('gallery', compact('gallerys'));
    }

    public function show($slug)
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();
        $gallery->foto = is_array($gallery->foto) ? $gallery->foto : explode(',', $gallery->foto);

        return view('gallery_show', compact('gallery'));
    }

}
