<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Berita\ArtikelRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ArtikelData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $categorys,
        public readonly string $artikel,
        public readonly UploadedFile $image,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(ArtikelRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getImage(),
            $request->getCategorys(),
            $request->getArtikel(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required|string' => 'Kolom Nama Artikel tidak boleh kosong!',
            'categorys.required' => 'Kolom Kategori tidak boleh kosong!',
            'artikel.required|string' => 'Kolom Isi Artikel tidak boleh kosong!',
            'image.required' => 'Kolom File Foto tidak boleh kosong!',
        ];
    }
}
