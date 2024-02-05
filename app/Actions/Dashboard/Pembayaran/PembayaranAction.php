<?php
namespace App\Actions\Dashboard\Pembayaran;

use App\Models\Pembayaran;


class PembayaranAction
{
    public function execute($pembayaranData)
    {
        $pembayaran = Pembayaran::updateOrCreate(
            ['order_id' => $pembayaranData->order_id],
            [
                'order_id' => rand(),
                'name' => $pembayaranData->name,
                'siswa_id' => $pembayaranData->siswa_id,
                'kelas_id' => $pembayaranData->kelas_id,
                'category_kelas' => $pembayaranData->category_kelas,
                'gross_amount' => $pembayaranData->gross_amount,
            ]
        );
    }
}


