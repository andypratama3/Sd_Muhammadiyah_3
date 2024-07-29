<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\HeroRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class HeroData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $desc,
        public readonly ?string $youtube,
        public readonly ?string $link,
        public readonly ?UploadedFile $image,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(HeroRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getImage(),
            $request->getDesc(),
            $request->getYoutube(),
            $request->getLink(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Artikel tidak boleh kosong!',
            'desc.required' => 'Kolom Deskripsi tidak boleh kosong!',
            'image.required' => 'Kolom File Foto tidak boleh kosong!',
        ];
    }
}
