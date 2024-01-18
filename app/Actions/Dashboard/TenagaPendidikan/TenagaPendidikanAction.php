<?php
namespace App\Actions\Dashboard\TenagaPendidikan;

use Illuminate\Support\Str;
use App\Models\TenagaPendidikan;


class TenagaPendidikanAction
{
    public function execute($tenagaPendidikanData): TenagaPendidikan
    {
        /*
            !handle Foto
        **/
        if($tenagaPendidikanData->foto){
            $tanagaPendidikan_picture = $tenagaPendidikanData->foto;
            $ext = $tanagaPendidikan_picture->getClientOriginalExtension();

            $upload_path = public_path('storage/img/tenagapendidikan/');
            $picture_name = 'T_Pendidikan_'.Str::slug($tenagaPendidikanData->name).'_'.date('YmdHis').".$ext";
            $tanagaPendidikan_picture->move($upload_path, $picture_name);
        }

        $tenagaPendidikan = TenagaPendidikan::updateOrCreate(
            ['slug' => $tenagaPendidikanData->slug],
            [
                'name' => $tenagaPendidikanData->name,
                'jabatan' => $tenagaPendidikanData->jabatan,
                'foto' => $picture_name,
            ],
        );
        return $tenagaPendidikan;
    }
}
