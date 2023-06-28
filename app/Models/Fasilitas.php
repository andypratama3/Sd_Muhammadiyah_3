<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    'foto'
    ];

    protected $dates = [
        'deleted_at'
    ];

    public function setNama_fasilitasAttribute($value)
    {
        $this->attributes['nama_fasilitas'] = $value;
        $this->attributes['slug'] = Str::slug($value). "-" .Str::random(4);
    }
}
