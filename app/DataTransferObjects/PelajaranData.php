<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\Dashboard\MatapelajaranRequest;

class PelajaranData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(MatapelajaranRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama tidak boleh kosong!',
        ];
    }
}
