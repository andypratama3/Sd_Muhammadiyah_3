<?php

namespace App\DataTransferObjects;

use App\Http\Requests\Karyawan\KaryawanRequest;
use Spatie\LaravelData\Data;

class KaryawanData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $role_id,
        public readonly ?string $slug,
    ) {
        //
    }

    public static function fromRequest(KaryawanRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getEmail(),
            $request->getRole(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom nama tidak boleh kosong!',
            'email.required' => 'Kolom email tidak boleh kosong!',
            'email.email' => 'Format email salah!',
            'email.unique' => 'Email yang Anda masukkan sudah terdaftar!',
            'role.required' => 'Pilih role!',
        ];
    }
}
