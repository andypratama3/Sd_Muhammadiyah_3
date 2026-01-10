<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use App\Models\StrukturTenagaPendidikan;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenagaPendidikan extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $tables = 'tenaga_pendidikans';

    protected $fillable = [
        'name',
        'jabatan',
        'foto',
        'slug',
        'struktur_tenaga_pendidikan_id',
    ];

    public function struktur_tenaga_pendidikan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StrukturTenagaPendidikan::class, 'struktur_tenaga_pendidikan_id', 'id');
    }
}
