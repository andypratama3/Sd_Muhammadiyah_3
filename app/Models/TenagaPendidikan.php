<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenagaPendidikan extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $tables = 'tenaga_pendidikans';

    protected $fillable = [
        'name',
        'jabatan',
        'foto',
        'slug',
    ];
}
