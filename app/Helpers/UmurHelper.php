<?php

namespace App\Http\Helpers;

class UmurHelper {

    public function Umur()
    {
        $umurs->transform(function ($umur) {
            $umur->umur = now()->diffInYears($umur->tanggal_lahir);
            return $umur;
        });
    }

}
