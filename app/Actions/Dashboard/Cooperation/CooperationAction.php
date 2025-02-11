<?php

namespace App\Actions\Dashboard\Cooperation;

use App\Models\Cooperation;
use Illuminate\Support\Str;
use App\DataTransferObjects\CooperationData;

class CooperationAction
{
    public function execute(CooperationData $cooperationData)
    {
        $picture_name = null;

        if($cooperationData->foto) {
            $foto = $cooperationData->foto;
            $ext = $foto->getClientOriginalExtension();

            //upload foto to folder
            $upload_path = public_path('storage/img/cooperation/');
            $picture_name = 'Cooperation_'.Str::slug($cooperationData->name).'_'.date('YmdHis').".$ext";
            $foto->move($upload_path, $picture_name);

        } else {
            $picture_name = Cooperation::where('slug', $cooperationData->slug)->first()->foto;
        }

        $cooperation = Cooperation::updateOrCreate(
            ['slug' => $cooperationData->slug],
            [
                'name' => $cooperationData->name,
                'foto' => $picture_name,
                'order' => $cooperationData->order
            ],

        );

        return $cooperation;
    }
}
