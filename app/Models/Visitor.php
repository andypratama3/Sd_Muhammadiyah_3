<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visitor extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'visitors';

    protected $fillable = [
        'ip_address',
        'user_agent',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Auto save visitor log once per day per IP
     * Dipanggil dari ViewsDataController@store
     */
    // public static function logOncePerDay()
    // {
    //    $ip = request()->header('CF-Connecting-IP')?? request()->ip();

    //     $userAgent = request()->userAgent();
    //     $today = now()->toDateString();

    //     // Cek apakah IP ini sudah tercatat hari ini
    //     $exists = self::where('ip_address', $ip)
    //         ->whereDate('date', $today)
    //         ->exists();

    //     if (!$exists) {
    //         self::create([
    //             'ip_address' => $ip,
    //             'date' => $today,
    //             'user_agent' => $userAgent,
    //         ]);

    //         return true; // Berhasil log visitor baru
    //     }

    //     return false; // IP sudah tercatat hari ini
    // }
    public static function logOncePerDay(): bool
    {
        $ip = request()->header('CF-Connecting-IP') ?? request()->ip();

        $today = now()->toDateString();

        // insertOrIgnore = ATOMIC (AMAN 1000+ REQUEST)
        $inserted = self::insertOrIgnore([
            'id'         => \Str::uuid(),
            'ip_address' => $ip,
            'user_agent' => substr(request()->userAgent(), 0, 255),
            'date'       => $today,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $inserted > 0;
    }

    /**
     * Get formatted date attribute
     * Contoh output: "25 December 2024"
     */
    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->date)->format('d F Y');
    }

    /**
     * Scope: Get visitors by specific date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope: Get visitors in date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get today's visitors
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    /**
     * Scope: Get this month's visitors
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('date', now()->year)
                     ->whereMonth('date', now()->month);
    }

    /**
     * Scope: Get this year's visitors
     */
    public function scopeThisYear($query)
    {
        return $query->whereYear('date', now()->year);
    }

}
