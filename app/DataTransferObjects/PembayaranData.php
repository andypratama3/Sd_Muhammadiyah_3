<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;


class ArtikelData extends Data
{
    public function __construct(
        public readonly string $siswa_id,
        public readonly string $kelas,
        public readonly string $category_kelas,
        public readonly string $gross_amount,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(ArtikelRequest $request): self
    {
        return self::from([
            $request->getSiswa(),
            $request->getKelas(),
            $request->getCategoryKelas(),
            $request->getSiswa(),
            $request->getGrossAmount(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'siswa.required' => 'Kolom Siswa tidak boleh kosong!',
            'kelas.required' => 'Kolom Kelas tidak boleh kosong!',
            'category_kelas.required' => 'Kolom Kategori Kelas tidak boleh kosong!',
            'gross_amount.required' => 'Kolom Total Pembayaran tidak boleh kosong!',
        ];
    }
}
