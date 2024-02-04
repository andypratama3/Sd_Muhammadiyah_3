<?php
namespace App\Actions\Dashboard\Pembayaran;

use App\Models\Pembayaran;


class PembayaranAction
{
    public function execute($pembayaranData)
    {
        $pembayaran = Pembayaran::updateOrCreate(
            ['slug' => $pembayaranData->slug],
            [
                'order_id' => rand(),
                'name' => $pembayaranData->name,
                'siswa_id' => $pembayaranData->siswa_id,
                'kelas' => $pembayaranData->kelas,
                'category_kelas' => $pembayaranData->category_kelas,
                'gross_amount' => $pembayaranData->gross_amount,
            ]
        );
    }
}


