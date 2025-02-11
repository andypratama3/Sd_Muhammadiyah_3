<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\CooperationRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CooperationData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?UploadedFile $foto,
        public readonly ?int $order,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(CooperationRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getFoto(),
            $request->getOrder(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required|string' => 'Kolom Nama tidak boleh kosong!',
            'image.required' => 'Kolom File Foto tidak boleh kosong!',
        ];
    }
}
