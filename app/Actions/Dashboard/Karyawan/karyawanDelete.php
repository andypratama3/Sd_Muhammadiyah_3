<?php
namespace App\Actions\Dashboard\Karyawan;


class karyawanDelete
{
    public function execute($karyawan)
    {
        $karyawan->user()->detach();
        $karyawan->delete();
        return $karyawan;
    }
}
