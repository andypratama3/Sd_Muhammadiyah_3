<?php

namespace App\Models;

use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StrukturTenagaPendidikan extends Model
{
    use HasFactory, HasUuids, NameHasSlug;

    protected $table = 'struktur_tenaga_pendidikan';

    protected $fillable = [
        'name',
        'slug',
        'struktur_tenaga_pendidikan_id',
    ];

    // Parent relationship
    public function parent()
    {
        return $this->belongsTo(StrukturTenagaPendidikan::class, 'struktur_tenaga_pendidikan_id');
    }

    // Children relationship
    public function children()
    {
        return $this->hasMany(StrukturTenagaPendidikan::class, 'struktur_tenaga_pendidikan_id')
                    ->with('children')
                    ->orderBy('name', 'asc');
    }

    // Get all tenaga pendidikan in this struktur
    public function tenaga_pendidikan()
    {
        return $this->hasMany(TenagaPendidikan::class, 'struktur_tenaga_pendidikan_id');
    }
}
