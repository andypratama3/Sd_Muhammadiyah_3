<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\StoreGuruRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GuruData extends Data
{
    public function __construct(
        public readonly string $description,
        public readonly string $lulusan,
        public readonly string $karyawan_id,
        public readonly array $pelajarans,
        public readonly ?UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(StoreGuruRequest $request): self
    {
        return self::from([
            $request->getFoto(),
            $request->getLulusan(),
            $request->getPelajarans(),
            $request->getKaryawan(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'description.required' => 'Kolom Nama Artikel tidak boleh kosong!',
            'pelajarans.required' => 'Kolom Pelajaran tidak boleh kosong!',
            'lulusan.required' => 'Kolom Isi Artikel tidak boleh kosong!',
            'foto.required' => 'Kolom File Foto tidak boleh kosong!',
        ];
    }
}
