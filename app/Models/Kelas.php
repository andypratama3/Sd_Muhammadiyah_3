<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Models\CategoryKelas;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    use SoftDeletes;
    protected $table = 'kelas';

    protected $fillable = [
        'name',
        'category_kelas',
        'slug',
    ];

    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'siswa_kelas');
    }
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class,'kelas','id');
    }
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'kelas', 'id');
    }


}
