<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KelengkapanFasilitas extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'kelengkapan_fasilitas';

    protected $fillable = [
        'nama',
        'fasilitas_id',
    ];

    public function fasilitas()
    {
        return $this->belongsTo(Fasilitas::class);
    }
}
