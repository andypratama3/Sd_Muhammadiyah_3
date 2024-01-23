<?php
namespace App\Actions\Dashboard\Guru;

use App\Models\Guru;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class UpdateGuruAction
{
    public function execute(Request $request, $slug)
    {
        $guru = Guru::where('slug',$slug)->firstOrFail();
        $guru->name = $request->name;
        $guru->karyawan_id = $request->karyawan_id;
        $guru->description = $request->description;
        $guru->lulusan = $request->lulusan;

        //delete picture after update
        if ($request->file('foto')) {
            Storage::disk('guru')->delete('storage/img/guru/'. $guru->foto);
            $guru_picture = $request->file('foto');
            $ext = $guru_picture->getClientOriginalExtension();

            $upload_path = public_path('guru');

            $picture_name = 'Guru_'.Str::slug($request->name).'_'.date('YmdHis').".$ext";
            $guru_picture->move($upload_path, $picture_name);
        }
        $guru->foto = $picture_name;
        $guru->update();
        foreach ($guru->pelajarans as $key => $pelajaran) {
            $pelajaranIds[] = $pelajaran->id;
        }

        $guru->pelajarans()->sync($pelajaranIds);


    }
}
