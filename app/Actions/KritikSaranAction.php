<?php
namespace App\Actions;

use App\Models\KritikSaran;

class KritikSaranAction
{
    public function execute($kritikSaranData)
    {

        $kritik_saran = new KritikSaran();
        $kritik_saran->name = $kritikSaranData->name;
        $kritik_saran->email = $kritikSaranData->email;
        $kritik_saran->subject = $kritikSaranData->subject;
        $kritik_saran->message = $kritikSaranData->message;
        $kritik_saran->save();

        return $kritik_saran;
    }

}
