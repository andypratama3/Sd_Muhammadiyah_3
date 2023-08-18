<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\Fasilitas\FasilitasRequest;

class FasilitasData extends Data
{
    public function __construct(
        public readonly string $nama_fasilitas,
        public readonly string $desc,
        public readonly UploadedFile $foto,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(FasilitasRequest $request): self
    {
        return self::from([
            $request->getNama_fasilitas(),
            $request->getDesc(),
            $request->getFoto(),
            $request->getSlug(),
        ]);
    }
    public static function messages()
    {
        return [
            'nama_fasilitas.required' => 'Kolom Nama Fasilitas Tidak Boleh Kosong!',
            'desc.required' => 'Kolom Deskripsi Tidak Boleh Kosong!',
            'foto.required' => 'Kolom Foto Tidak Boleh Kosong!',
        ];
    }
}

