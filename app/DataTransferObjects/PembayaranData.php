<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\Pembayaran\PembayaranRequest;


class PembayaranData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $siswa_id,
        public readonly string $kelas_id,
        public readonly string $category_kelas,
        public readonly string $gross_amount,
        public readonly ?string $order_id,

    ) {
        //
    }

    public static function fromRequest(PembayaranRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getSiswa(),
            $request->getKelas(),
            $request->getCategoryKelas(),
            $request->getSiswa(),
            $request->getGrossAmount(),
            $request->getOrderID(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Judul tidak boleh kosong!',
            'siswa.required' => 'Kolom Siswa tidak boleh kosong!',
            'kelas_id.required' => 'Kolom Kelas tidak boleh kosong!',
            'category_kelas.required' => 'Kolom Kategori Kelas tidak boleh kosong!',
            'gross_amount.required' => 'Kolom Total Pembayaran tidak boleh kosong!',
        ];
    }
}
