<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $gallerys = Gallery::select('name','foto','slug','cover')->orderBy('created_at','desc')->paginate(8);
        return view('gallery', compact('gallerys'));
    }

    public function show($slug)
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();
        $gallery->foto = is_array($gallery->foto) ? $gallery->foto : explode(',', $gallery->foto);

        $gallery->video_id = $this->extractYoutubeId($gallery->link);

        // dd($gallery->video_id);

        return view('gallery_show', compact('gallery'));
    }

    private function extractYoutubeId($url)
    {
        preg_match('/(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/watch\?v=|\/v\/|\/.+\?v=))([a-zA-Z0-9_-]{11})/', $url, $matches);
        return $matches[1] ?? null;
    }


}
