<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\Dashboard\ProfileUpdateRequest;



class ProfileData extends Data
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $sex,
        public readonly ?string $phone,
        public readonly ?string $email,

    ) {
        //
    }

    public static function fromRequest(ProfileUpdateRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getJk(),
            $request->getPhone(),
            $request->getEmail(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom Nama tidak boleh kosong!',
            'sex.required' => 'Kolom Jenis kelamin tidak boleh kosong!',
            'phone.required' => 'Kolom No Hp tidak boleh kosong!',
            'email.required' => 'Kolom Email tidak boleh kosong!',
            'email.unique' => 'Kolom Email boleh sama kosong!',
        ];
    }
}
