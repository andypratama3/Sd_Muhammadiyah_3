<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelajaran extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $tables = 'pelajarans';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function gurus()
    {
        return $this->belongsToMany(Guru::class, 'guru_matapelajaran');
    }
}
