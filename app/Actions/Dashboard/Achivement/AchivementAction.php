<?php

namespace App\Actions\Dashboard\achivement;

use App\Models\Achivement;
use Illuminate\Support\Str;
use App\DataTransferObjects\AchivementData;

class achivementAction
{
    public function execute(AchivementData $achivementData)
    {
        $picture_name = null;

        if($achivementData->foto) {
            $foto = $achivementData->foto;
            $ext = $foto->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/achivement/');
            $picture_name = 'achivement_'.Str::slug($achivementData->name).'_'.date('YmdHis').".$ext";
            $foto->move($upload_path, $picture_name);

        } else {
            $picture_name = Achivement::where('slug', $achivementData->slug)->first()->foto;
        }

        $achivement = Achivement::updateOrCreate(
            ['slug' => $achivementData->slug],
            [
                'name' => $achivementData->name,
                'foto' => $picture_name,
                'order' => $achivementData->order
            ],

        );

        return $achivement;
    }
}
