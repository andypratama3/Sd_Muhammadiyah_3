<?php

namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request;

class UpdateFasilitasAction
{
    public function execute(Request $request, $slug)
    {
        $fasilitas = Fasilitas::where('slug', $slug)->firstOrFail();
        $fasilitas->nama_fasilitas = $request->nama_fasilitas;
        $fasilitas->desc = $request->desc;
        if ($request->file('foto')) {
            $fasilitas_picture = $request->file('foto');
            $ext = $fasilitas_picture->getClientOriginalExtension();

            $upload_path = 'storage/img/fasilitas/';
            $picture_name = 'Fasilitas_'.Str::slug($request->nama_fasilitas).'_'.date('YmdHis').".$ext";
            $fasilitas_picture->move($upload_path, $picture_name);
        }
        $fasilitas->foto = $picture_name;
        $fasilitas->save();
    }
}
