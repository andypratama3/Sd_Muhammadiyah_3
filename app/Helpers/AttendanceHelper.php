<?php

namespace App\Helpers;

class AttendanceHelper
{
    public static function badgeColor($status)
    {
        return match ($status) {
            'hadir' => 'success',
            'izin' => 'warning text-dark',
            'sakit', 'alpa' => 'danger',
            default => 'secondary',
        };
    }
}
