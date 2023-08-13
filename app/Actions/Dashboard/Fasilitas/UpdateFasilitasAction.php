<?php
namespace App\Actions\Dashboard\Fasilitas;

use App\Models\Fasilitas;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Request;

class UpdateFasilitasAction
{
    public function execute(Request $request, $slug)
{
    $fasilitas = Fasilitas::where('slug', $slug)->firstOrFail();
    $old_image_names = explode(',', $fasilitas->foto);
    $fasilitas->nama_fasilitas = $request->nama_fasilitas;
    $fasilitas->desc = $request->desc;

    $upload_path = 'storage/img/fasilitas/';

    // Delete old images before updating the foto field
    foreach ($old_image_names as $old_image_name) {
        Storage::disk('fasilitas')->delete('storage/img/fasilitas/' . $old_image_name);
    }

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
