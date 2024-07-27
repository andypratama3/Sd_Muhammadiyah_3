<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\KritikSaranRequest;

class KritikSaranData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $subject,
        public readonly string $email,
        public readonly string $message,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(KritikSaranRequest $request): self
    {
        return self::from([
            $request->getName(),
            $request->getSubject(),
            $request->getEmail(),
            $request->getMessage(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'name.required|string' => 'Kolom Nama tidak boleh kosong!',
            'subject.required|string' => 'Kolom Subject tidak boleh kosong!',
            'email.required|string' => 'Kolom Email tidak boleh kosong!',
            'message.required|string' => 'Kolom Kritik Dan Saran tidak boleh kosong!',
        ];
    }
}
