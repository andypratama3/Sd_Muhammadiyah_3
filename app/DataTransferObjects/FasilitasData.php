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
        public readonly ?string $ukuran,
        public readonly ?string $kapasitas,
        public readonly ?array $foto,
        public readonly array $kelengkapan,
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
            $request->getUkuran(),
            $request->getKapasitas(),
            $request->getKelengkapan(),
            $request->getSlug(),
        ]);
    }
    public static function messages()
    {
        return [
            'nama_fasilitas.required' => 'Kolom Nama Fasilitas Tidak Boleh Kosong!',
            'desc.required' => 'Kolom Deskripsi Tidak Boleh Kosong!',
        ];
    }
}

