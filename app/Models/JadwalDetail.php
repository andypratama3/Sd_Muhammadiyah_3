<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalDetail extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'jadwal_detail';

    protected $fillable = [
        'hari',
        'time_start',
        'time_end',
        'pelajaran_id',
        'guru_id',
        'jadwal_id',
        'color',
    ];

    public function pelajaran()
    {
        return $this->belongsTo(Pelajaran::class, 'pelajaran_id', 'id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'id');
    }
}
