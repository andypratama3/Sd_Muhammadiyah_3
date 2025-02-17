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
        'transaction_status',
        'type_payment',
        'category_payment_id',
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
