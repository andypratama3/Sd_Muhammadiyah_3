<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\PrestasiRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PrestasiData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $status,
        public readonly UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(PrestasiRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getFoto(),
            $request->getDesc(),
            $request->getStatus(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Prestasi tidak boleh kosong!',
            'description.required' => 'Kolom Deskripsi Prestasi tidak boleh kosong!',
            'foto.required' => 'Kolom Foto tidak boleh kosong!',
            'status.required' => 'Kolom Status Prestasi tidak boleh kosong!',
        ];
    }
}
