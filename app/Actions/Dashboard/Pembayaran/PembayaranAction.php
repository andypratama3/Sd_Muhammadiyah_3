<?php
namespace App\Actions\Dashboard\Pembayaran;


class PembayaranAction
{
    public function execute($pembayaranData)
    {
        $pembayaran = Pembayaran::updateOrCreate(
            ['slug' => $pembayaranData->slug],
            [
                'order_id' => $pembayaranData->rand(),
                'siswa_id' => $pembayaranData->siswa_id,
                'kelas' => $pembayaranData->kelas,
                'category_kelas' => $pembayaranData->category_kelas,
                'gross_amount' => $pembayaranData->gross_amount,
            ]
        );
    }
}


