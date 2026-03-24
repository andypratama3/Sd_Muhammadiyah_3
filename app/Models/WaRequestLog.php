<?php
// app/Models/WaRequestLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaRequestLog extends Model
{
    protected $fillable = [
        'phone',
        'nisn',
        'siswa_id',
        'status',
        'ip_address',
        'response_time_ms',
        'error_message',
        'requested_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
    ];
}