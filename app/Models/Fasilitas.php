<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\HasPermissionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fasilitas extends Model
{
    use HasFactory;
    use UsesUuid;
    use HasPermissionsTrait;
    use SoftDeletes;


    protected $table = 'fasilitas';

    protected $guarded = ['id'];

    protected $fillable = [
        'nama_fasilitas',
        'desc',
        'foto',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function setNamafasilitasAttribute($value)
    {
        $this->attributes['nama_fasilitas'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }
}
