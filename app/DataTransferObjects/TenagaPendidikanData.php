<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\TenagaPendidikanRequest;


class TenagaPendidikanData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $jabatan,
        public readonly UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(TenagaPendidikanRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getJabatan(),
            $request->getFoto(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama tidak boleh kosong!',
            'jabatan.required' => 'Kolom Jabatan tidak boleh kosong!',
            'foto.required' => 'Kolom Foto tidak boleh kosong!',
        ];
    }
}
