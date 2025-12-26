<?php

namespace App\Models;

use App\Models\Prestasi;
use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriPrestasi extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'kategori_prestasi';

    protected $fillable = [
        'name'
    ];

    public function prestasi_kategori()
    {
        return $this->belongsToMany(Prestasi::class, 'prestasi_kategori','kategori_prestasi_id','prestasi_id');
    }
}
