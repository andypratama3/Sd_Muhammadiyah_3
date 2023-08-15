<?php

namespace App\DataTransferObjects;

use App\Http\Requests\Fasilitas\FasilitasRequest;
use Spatie\LaravelData\Data;

class FasilitasData extends Data
{
    public function __construct(
        public readonly string $nama_fasilitas,
        public readonly string $desc,
        public readonly string $foto,
    ){
        //
    }

    public static function fromRequest(FasilitasRequest $request): self
    {
        return self::from([
            $request->getNama_fasilitas(),
            $request->getDesc(),
            $request->getFoto(),
        ]);
    }
    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Fasilitas Tidak Boleh Kosong!',
            'desc.required' => 'Kolom Deskripsi Tidak Boleh Kosong!',
            'foto.required' => 'Kolom Foto Tidak Boleh Kosong!',

        ];
    }
}

