<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visitor extends Model
{
    use HasFactory;

    use HasUuids;

    protected $table = 'visitors';

    protected $fillable = [
        'ip_address',
        'user_agent',
        'date',
    ];

    // auto save when have request from visitor
    public static function logOncePerDay()
    {
        $ip = request()->ip();
        $today = now()->toDateString();

        $exists = self::where('ip_address', $ip)
            ->whereDate('date', $today)
            ->exists();

        if (!$exists) {
            $visitor = new self();
            $visitor->save(); // memicu creating
        }
    }



    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d F Y');
    }


}
