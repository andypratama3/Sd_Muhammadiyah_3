<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Berita\BeritaRequest;

class BeritaData extends Data
{
    public function __construct(
        public readonly string $judul,
        public readonly string $desc,
        public readonly UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(BeritaRequest $request): self
    {
        return self::from([
            $request->getJudul(),
            $request->getDesc(),
            $request->getFoto(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'judul.required' => 'Kolom Nama tidak boleh kosong!',
            'desc.required' => 'Kolom Deskripsi tidak boleh kosong!',
            'foto.required' => 'Kolom Foto tidak boleh kosong!',
        ];
    }
}
