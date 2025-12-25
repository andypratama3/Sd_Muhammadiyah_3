<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rapot extends Model
{
    use HasFactory, UsesUuid;

    protected $table = 'rapot';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'angkatan',
        'tahun',
        'catatan',
        'file_rapot',
    ];


    /**
     * Get the siswa that owns the Rapot
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    /**
     * Get the kelas that owns the Rapot
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }
}
