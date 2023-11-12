<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Esktrakurikuler\EsktrakurikulerRequest;

class EsktrakurikulerData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $desc,
        public readonly UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(EsktrakurikulerRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getDesc(),
            $request->getFoto(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama tidak boleh kosong!',
            'desc.required' => 'Kolom Deskripsi tidak boleh kosong!',
            'foto.required' => 'Kolom Foto tidak boleh kosong!',
        ];
    }
}
