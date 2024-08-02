<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\GalleryRequest;



class GalleryData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(GalleryRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getFoto(),
            $request->getSlug(),
        ]);
    }
    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Foto Tidak Boleh Kosong!',
            'foto.required' => 'Kolom Foto Tidak Boleh Kosong!',
        ];
    }
}

