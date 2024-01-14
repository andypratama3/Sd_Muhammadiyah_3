<?php
namespace App\Actions\Dashboard\Pelajaran;

use App\Models\Pelajaran;

class PelajaranActionDelete
{
    public function execute($slug)
    {
        $pelajaran = Pelajaran::where('slug')->firstOrFail();
        $pelajaran->delete();
    }
}
