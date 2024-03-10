<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JudulPembayaran extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    
    protected $table = 'judulpembayarans';
    protected $fillable = [
        'name',
        'slug',
    ];
}
