<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Berita extends Model
{
    use HasFactory;


    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;
    use HasFactory;
    protected $table = 'beritas';
    protected $guarded = ['id'];
    protected $fillable = [
    'judul',
    'desc',
    'foto'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function setJudulAttribute($value)
    {
        $this->attributes['judul'] = $value;
        $this->attributes['slug'] = Str::slug($value). "-" .Str::random(4);
    }
}
