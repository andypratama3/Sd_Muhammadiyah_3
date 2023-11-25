<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\Kelas\KelasRequest;

class KelasData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly array $category_kelas,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(KelasRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getCategoryKelas(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama Kelas tidak boleh kosong!',
            'category_kelas.required' => 'Kolom Kategori Kelas Harus Memiliki Kategori!',
        ];
    }
}
