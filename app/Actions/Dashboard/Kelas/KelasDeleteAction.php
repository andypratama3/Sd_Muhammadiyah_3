<?php
namespace App\Actions\Dashboard\Kelas;

use App\Models\Kelas;


class KelasDeleteAction{
    public function execute($slug)
    {
        $kelas = Kelas::where('slug', $slug)->firstOrFail();
        $kelas->delete();
    }
}
