<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use App\Models\KategoriPrestasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prestasi extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;

    protected $primaryKey = 'id';
    protected $table = 'prestasis';

    protected $fillable = [
        'name',
        'description',
        'foto',
        'status',
        'tingkat',
        'penyelenggara',
        'tanggal',
        'views',
        'juara',
        'slug',
    ];

    const STATUS_SISWA = 1;
    const STATUS_SEKOLAH = 2;

    // ===== RELATIONSHIPS =====
    public function prestasi_kategori()
    {
        return $this->belongsToMany(KategoriPrestasi::class, 'prestasi_kategori','prestasi_id','kategori_prestasi_id');
    }

    // ===== SCOPES =====

    /**
     * Filter Prestasi Siswa
     */
    public function scopeSiswa($query)
    {
        return $query->where('status', self::STATUS_SISWA);
    }

    /**
     * Filter Prestasi Sekolah
     */
    public function scopeSekolah($query)
    {
        return $query->where('status', self::STATUS_SEKOLAH);
    }

    /**
     * Filter hanya yang memiliki kategori
     */
    public function scopeWithCategories($query)
    {
        return $query->whereHas('prestasi_kategori');
    }

    /**
     * Filter Prestasi Siswa yang memiliki kategori
     */
    public function scopeSiswaWithCategories($query)
    {
        return $query->siswa()->withCategories();
    }

    /**
     * Filter Prestasi Sekolah yang memiliki kategori
     */
    public function scopeSekolahWithCategories($query)
    {
        return $query->sekolah()->withCategories();
    }

    public function incrementClickCount()
    {
        $this->views++;
        $this->save();
    }

    // ===== METHODS =====

    /**
     * Check apakah ini prestasi siswa
     */
    public function isSiswa()
    {
        return $this->status == self::STATUS_SISWA;
    }

    /**
     * Check apakah ini prestasi sekolah
     */
    public function isSekolah()
    {
        return $this->status == self::STATUS_SEKOLAH;
    }

    /**
     * Get kategori prestasi siswa yang unik
     */
    public static function getSiswaCategories()
    {
        return KategoriPrestasi::whereHas('prestasi_kategori', function ($query) {
            $query->where('status', Prestasi::STATUS_SISWA);
        })->get();
    }


    /**
     * Get kategori prestasi sekolah yang unik
     */
    public static function getSekolahCategories()
    {
        return KategoriPrestasi::whereHas('prestasi_kategori', function ($query) {
            $query->sekolah();
        })->get();
    }

    /**
     * Count Prestasi Siswa dengan kategori
     */
    public static function countSiswaWithCategories()
    {
        return self::siswaWithCategories()->count();
    }

    /**
     * Count Prestasi Sekolah dengan kategori
     */
    public static function countSekolahWithCategories()
    {
        return self::sekolahWithCategories()->count();
    }
}
