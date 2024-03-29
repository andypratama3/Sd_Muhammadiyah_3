<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use App\Http\Traits\NameHasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembayaran extends Model
{
    use HasFactory;
    use UsesUuid;
    protected $table = 'pembayarans';

    protected $fillable = [
        'order_id',
        'siswa_id',
        'kelas_id',
        'category_kelas',
        'email',
        'gross_amount',
        'startdate',
        'enddate',
        'type_payment',
        'bulkId',
        'SessionID',
        'Url',
        'trx_id',
        'account_id',
        'judul_id',
        'status',
        'slug',
    ];
    /*
        ! relation with siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }
    public function judul()
    {
        return $this->belongsTo(JudulPembayaran::class, 'judul_id', 'id');
    }


}
