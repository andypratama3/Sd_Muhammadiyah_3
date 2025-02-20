<?php

namespace App\Models;

use App\Http\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Charge extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    protected $table = 'charges';

    protected $fillable = [
        'name' ,
        'type_payment',
        'order_id' ,
        'order_id_1',
        'siswa_id' ,
        'gross_amount' ,
        'payment_type' ,
        'bank' ,
        'va_number' ,
        'transaction_id' ,
        'transaction_time' ,
        'fraud_status' ,
        'transaction_status' ,
        'category_payment_id' ,
        'snap_token' ,
    ];



    protected $dates = ['deleted_at'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kategori_pembayaran()
    {
        return $this->belongsTo(JudulPembayaran::class, 'category_payment_id', 'id');
    }
}
