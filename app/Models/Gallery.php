<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gallery extends Model
{
    use HasFactory, UsesUuid, NameHasSlug;

    protected $table = 'galleries';

    protected $fillable = [
        'name',
        'foto',
        'cover',
        'link',
        'slug',
    ];

    public function gallery_kategori()
    {
        return $this->belongsToMany(KategoriGallery::class, 'gallery_kategori');
    }
}
