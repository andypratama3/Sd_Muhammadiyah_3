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
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->ip_address = request()->ip();
            $model->user_agent = request()->header('User-Agent');
            $model->date = now();
        });
    }


    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d F Y');
    }


}
