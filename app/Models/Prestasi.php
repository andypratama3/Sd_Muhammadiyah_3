<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
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
}
