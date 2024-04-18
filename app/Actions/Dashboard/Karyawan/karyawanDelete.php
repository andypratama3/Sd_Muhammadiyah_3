<?php
namespace App\Actions\Dashboard\Karyawan;


class karyawanDelete
{
    public function execute($karyawan)
    {
        $karyawan->user()->delete();
        $karyawan->delete();
        return $karyawan;
    }
}
