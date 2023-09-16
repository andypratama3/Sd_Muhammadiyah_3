<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;
    use UsesUuid;
    use SoftDeletes;
    protected $tables = 'kelas';

    protected $fillable = [
        'name',
        'slug',
    ];
}
