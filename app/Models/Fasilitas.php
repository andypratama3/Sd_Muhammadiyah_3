<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Fasilitas extends Model
{
    use HasFactory;
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;
    use HasFactory;

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
