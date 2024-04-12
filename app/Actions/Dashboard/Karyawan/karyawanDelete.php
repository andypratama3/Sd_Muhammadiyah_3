<?php
namespace App\Actions\Dashboard\Karyawan;


class karyawanDelete
{
    public function execute($karyawan)
    {
        $karyawan->delete();
        return $karyawan;
    }
}
