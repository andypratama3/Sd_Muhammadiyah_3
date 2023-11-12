<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Esktrakurikuler extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    use SoftDeletes;

    protected $table = 'ekstrakulikulers';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'desc',
        'foto',
    ];

}
