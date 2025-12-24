<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use App\Models\KategoriPrestasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prestasi extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $tables = 'prestasis';

    protected $fillable = [
        'name',
        'description',
        'foto',
        'status',
        'slug',
    ];

    public function prestasi_kategori()
    {
        return $this->belongsToMany(KategoriPrestasi::class, 'prestasi_kategori', 'prestasi_id', 'kategori_prestasi_id');
    }
}
