<?php

namespace App\DataTransferObjects;

use App\Http\Requests\UserSettings\UpdateUserPasswordRequest;
use Spatie\LaravelData\Data;

class UserPasswordData extends Data
{
    public function __construct(
        public readonly string $current_password,
        public readonly string $new_password,
        public readonly string $confirm_new_password,
    ) {
        //
    }

    public static function fromRequest(UpdateUserPasswordRequest $request): self
    {
        return self::from([
            $request->getCurrentPassword(),
            $request->getNewPassword(),
            $request->getConfirmNewPassword(),
        ]);
    }

    public static function messages()
    {
        return [
            'current_password.required' => 'Password saat ini tidak boleh kosong!',
            'new_password.required' => 'Password baru tidak boleh kosong!',
            'confirm_new_password.required_with' => 'Konfirmasi password tidak boleh kosong!',
            'confirm_new_password.same' => 'Password baru tidak sama!',
        ];
    }
}
