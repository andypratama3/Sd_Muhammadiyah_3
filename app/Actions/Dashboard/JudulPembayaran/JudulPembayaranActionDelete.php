<?php
namespace App\Actions\Dashboard\JudulPembayaran;

use App\Models\JudulPembayaran;

class JudulPembayaranActionDelete
{
    public function execute($slug)
    {
        $judulPembayaran = JudulPembayaran::where('slug',$slug)->firstOrFail();
        $judulPembayaran->delete();
    }
}
