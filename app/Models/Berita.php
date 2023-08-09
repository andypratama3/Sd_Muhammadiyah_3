<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Berita extends Model
{
    use HasFactory;
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

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

    public function setJudulAttribute($value)
    {
        $this->attributes['judul'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
