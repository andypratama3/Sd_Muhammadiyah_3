<?php
namespace App\Actions\Dashboard\Guru;

use App\Models\Guru;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class StoreGuruAction
{
    public function execute(Request $request)
    {
        $guru = new Guru;
        $guru->name = $request->name;
        $guru->description = $request->description;
        $guru->lulusan = $request->lulusan;

        if ($request->file('foto')) {
            $guru_picture = $request->file('foto');
            $ext = $guru_picture->getClientOriginalExtension();

            $upload_path = 'storage/img/guru/';
            $picture_name = 'Guru_'.Str::slug($request->name).'_'.date('YmdHis').".$ext";
            $guru_picture->move($upload_path, $picture_name);
        }
        $guru->foto = $picture_name;
        $guru->save();

    }
}
