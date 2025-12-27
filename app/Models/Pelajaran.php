<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pelajaran extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $table = 'pelajarans';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function gurus(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_matapelajaran', 'pelajaran_id', 'guru_id');
    }
}
