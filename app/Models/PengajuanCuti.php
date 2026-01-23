<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    protected $table = 'pengajuan_cuti';

    protected $fillable = [
        'karyawan_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'file_pendukung',
        'status',
        'disetujui_oleh',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function karyawan() // UBAH: pegawai → karyawan
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh', 'id');
    }
}
