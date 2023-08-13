<?php

namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreFasilitasAction
{
    public function execute(Request $request)
    {
        $fasilitas = new Fasilitas();
        $fasilitas->nama_fasilitas = $request->nama_fasilitas;
        $fasilitas->desc = $request->desc;

        $upload_path = 'storage/img/fasilitas/';

        $picture_names = [];

        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $fasilitas_picture) {
                $ext = $fasilitas_picture->getClientOriginalExtension();
                $picture_name = 'fasilitas_' . Str::slug($request->nama_fasilitas) . '_' . date('YmdHis') . "_" . uniqid() . ".$ext";
                $fasilitas_picture->move($upload_path, $picture_name);
                $picture_names[] = $picture_name;
            }
        }

        $fasilitas->foto = implode(',', $picture_names);
        $fasilitas->save();
    }

}
