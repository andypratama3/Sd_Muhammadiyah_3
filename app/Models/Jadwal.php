<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jadwal extends Model
{
    use HasFactory;
    use UsesUuid;
    use NameHasSlug;
    protected $table = 'jadwals';
    protected $fillable = [
        'tahun_ajaran',
        'jadwal',
        'kelas',
        'category_kelas',
    ];
    public function kelas_jadwal()
    {
        return $this->belongsTo(Kelas::class,'kelas', 'id');
    }


}
