<?php

namespace App\DataTransferObjects;

use App\Http\Requests\Task\TaskRequest;
use Spatie\LaravelData\Data;

class TaskData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly array $permissions,
        public readonly ?string $slug,
    ) {
        //
    }

    public static function fromRequest(TaskRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getDescription(),
            $request->getPermissions(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required' => 'Kolom nama tidak boleh kosong!',
            'description.required' => 'Kolom deskripsi tidak boleh kosong!',
            'permissions.required' => 'Pilih minimal 1 izin akses default atau tambah custom permission!',
        ];
    }
}
