<?php

namespace App\Actions\Dashboard\Guru;

use App\Models\Guru;

class DeleteGuruAction
{
    public function execute($slug): void
    {
        $guru = Guru::where('slug', $slug)->firstOrFail();
        $guru->delete();
    }
}
