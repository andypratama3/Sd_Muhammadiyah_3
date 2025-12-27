<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guru extends Model
{
    use UsesUuid;
    use HasFactory;

    protected $table = 'gurus';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'karyawan_id',
        'description',
        'lulusan',
        'foto',
        'slug'
    ];

    // FIX: Seharusnya BelongsTo, bukan HasOne
    // Karena guru belongs to karyawan
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value).'-'.Str::random(4);
    }

    // Tambahkan type hint untuk clarity
    public function pelajarans(): BelongsToMany
    {
        return $this->belongsToMany(Pelajaran::class, 'guru_matapelajaran', 'guru_id', 'pelajaran_id');
    }

    // Take value slug in model for route binding
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
