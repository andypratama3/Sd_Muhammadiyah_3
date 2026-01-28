<?php

namespace App\Models;

use App\Models\Prestasi;
use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriPrestasi extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'kategori_prestasi';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function prestasi_kategori()
    {
        return $this->belongsToMany(Prestasi::class, 'prestasi_kategori','kategori_prestasi_id','prestasi_id');
    }
}
