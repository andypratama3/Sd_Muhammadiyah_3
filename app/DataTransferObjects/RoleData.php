<?php

namespace App\DataTransferObjects;

use App\Http\Requests\Role\RoleRequest;
use Spatie\LaravelData\Data;

class RoleData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly array $permissions,
        public readonly ?string $slug,
    ) {
        //
    }

    public static function fromRequest(RoleRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getPermissions(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom nama tidak boleh kosong!',
            'permissions.required' => 'Pilih minimal 1 izin akses !',
        ];
    }
}
