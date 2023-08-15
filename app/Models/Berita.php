<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berita extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    use HasPermissionsTrait;
    use SoftDeletes;

    protected $table = 'beritas';

    protected $guarded = ['id'];

    protected $fillable = [
        'judul',
        'desc',
        'foto',
    ];

    protected $dates = [
        'deleted_at',
    ];

}
