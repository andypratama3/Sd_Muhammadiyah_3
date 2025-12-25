<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\GalleryRequest;



class GalleryData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?array $foto,
        public readonly ?UploadedFile $cover,
        public readonly array $gallery_kategori,
        public readonly ?string $link,
        public readonly ?string $slug,

    ) {
        //
    }


    public static function fromRequest(GalleryRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getFoto(),
            $request->getCover(),
            $request->getLink(),
            $request->getGalleryKategori(),
            $request->getSlug(),
        ]);
    }
    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Foto Tidak Boleh Kosong!',
            // 'foto.required' => 'Kolom Foto Tidak Boleh Kosong!',
            'cover.required' => 'Cover Untuk Gallery Tidak Boleh Kosong',
        ];
    }
}

