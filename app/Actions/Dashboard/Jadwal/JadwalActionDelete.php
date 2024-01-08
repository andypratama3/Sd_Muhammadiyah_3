<?php
namespace App\Actions\Dashboard\Jadwal;

use App\Models\Jadwal;


class JadwalActionDelete
{
    public function execute($slug)
    {
        $jadwal = Jadwal::where('slug', $slug)->firstOrFail();
        $jadwal->delete();

    }
}
