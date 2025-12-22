<?php
namespace App\Actions\Dashboard\Jadwal;

use App\Models\Jadwal;


class JadwalActionDelete
{
    public function execute($slug)
    {

        $jadwal = Jadwal::where('id', $slug)->firstOrFail();
        $jadwal->jadwal_details()->delete();
        $jadwal->delete();

    }
}
