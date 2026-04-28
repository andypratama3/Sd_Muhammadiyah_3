<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapAbsensiHistory extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'zip_file_path',
        'zip_filename',
        'status',
        'file_per_karyawan',
        'keterangan',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'file_per_karyawan' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDateRangeLabelAttribute(): string
    {
        return $this->start_date->locale('id')->translatedFormat('d F Y') . ' - ' .
               $this->end_date->locale('id')->translatedFormat('d F Y');
    }

    public function getFilePerKaryawanAttribute($value): array
    {
        return json_decode($value, true) ?? [];
    }
}
