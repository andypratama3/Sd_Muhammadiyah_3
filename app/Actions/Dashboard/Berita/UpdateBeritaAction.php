<?php

namespace App\Actions\Dashboard\Berita;

use App\Models\Berita;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class UpdateBeritaAction
{
    public function execute(Request $request, $slug)
    {
        $berita = Berita::where('slug', $slug)->firstOrFail();
        $berita->judul = $request->judul;
        $berita->desc = $request->desc;

        if ($request->file('foto')) {
            Storage::disk('berita')->delete('storage/img/berita/'. $berita->foto);
            $berita_picture = $request->file('foto');
            $ext = $berita_picture->getClientOriginalExtension();

            $upload_path = 'storage/img/berita/';
            $picture_name = 'Berita_'.Str::slug($request->judul).'_'.date('YmdHis').".$ext";
            $berita_picture->move($upload_path, $picture_name);
        }
        $berita->foto = $picture_name;
        // $berita->save();
        
    }
}
