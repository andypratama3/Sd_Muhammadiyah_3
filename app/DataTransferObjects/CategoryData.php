<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\Category\CatagoryRequest;

// use App\Http\Requests\Category\CategoryRequest;

class CategoryData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(CatagoryRequest $request): self
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
