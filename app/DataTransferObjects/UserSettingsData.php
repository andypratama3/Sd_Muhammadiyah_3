<?php

namespace App\DataTransferObjects;

use App\Http\Requests\UserSettings\UpdateUserProfileRequest;
use Spatie\LaravelData\Data;

class UserSettingsData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {
        //
    }

    public static function fromRequest(UpdateUserProfileRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getEmail(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom nama tidak boleh kosong!',
            'email.required' => 'Kolom email tidak boleh kosong!',
            'email.email' => 'Format email salah!',
            'email.unique' => 'Email yang Anda masukkan sudah terdaftar!',
        ];
    }
}
