<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Spmb extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'spmbs';

    protected $fillable = [
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'suku',
        'alamat',
        'nama_asal_sekolah',
        'sttb',
        'alamat_sekolah',
        'select_data',
        'nama_ayah',
        'nama_ibu',
        'pendidikan_ayah',
        'pendidikan_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'alamat_ayah',
        'alamat_ibu',
        'nama_wali',
        'pekerjaan_wali',
        'alamat_wali',
        'file_sttb',
        'akta_kelahiran',
        'kk',
        'pas_foto',
        'status_pembayaran',
        'phone',
        'nomor_urut',
        'status_pembayaran',
        'order_id',
    ];

}
