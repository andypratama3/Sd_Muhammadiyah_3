<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\Berita\ArtikelRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CommentData extends Data
{
    public function __construct(
        public readonly string $comment,
        public readonly string $artikel,
        public readonly string $user,
        public readonly ?string $slug,

    ) {
        //
    }

    public static function fromRequest(CommentRequest $request): self
    {
        return self::from([
            $request->getComment(),
            $request->getArtikel(),
            $request->getUser(),
            $request->getSlug(),
        ]);
    }

    public static function messages()
    {
        return [
            'comment.required' => 'Kolom Komentar Artikel tidak boleh kosong!',
        ];
    }
}
