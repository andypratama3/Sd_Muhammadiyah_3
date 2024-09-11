<?php

namespace App\Actions\Dashboard\Siswa;

class SiswaActionDelete
{
    public function execute($siswa)
    {
        $siswa->delete();
    }
}
