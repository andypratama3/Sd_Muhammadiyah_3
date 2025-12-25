<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriGallery extends Model
{
    use HasFactory, UsesUuid;
    protected $table = 'kategori_gallery';

    protected $fillable = [
        'name',
    ];

    public function galleries()
    {
        return $this->belongsToMany(Gallery::class, 'gallery_kategori');
    }
}
