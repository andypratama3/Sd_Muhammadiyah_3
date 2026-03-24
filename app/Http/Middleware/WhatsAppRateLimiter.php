<?php
// app/Http/Middleware/WhatsAppRateLimiter.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WhatsAppRateLimiter
{
    /**
     * Batas request:
     *  - Per nomor WA : 5 / menit
     *  - Per nomor WA : 20 / jam
     *  - Global       : 100 / menit
     */
    public function handle(Request $request, Closure $next): Response
    {
        $phone = $request->input('phone', 'unknown');

        // --- 1. Global rate limit ---
        $globalKey = 'wa_global_ratelimit';
        if (RateLimiter::tooManyAttempts($globalKey, 100)) {
            Log::warning('Global WA rate limit exceeded');
            return $this->rateLimitResponse('Sistem sedang sibuk, coba beberapa saat lagi.');
        }
        RateLimiter::hit($globalKey, 60); // window 60 detik

        // --- 2. Per-nomor, per menit ---
        $perMinuteKey = 'wa_ratelimit_min:' . $phone;
        if (RateLimiter::tooManyAttempts($perMinuteKey, 5)) {
            $seconds = RateLimiter::availableIn($perMinuteKey);
            Log::info('Per-minute rate limit', ['phone' => $this->maskPhone($phone)]);
            return $this->rateLimitResponse(
                "Terlalu banyak permintaan. Coba lagi dalam{$seconds} detik."
            );
        }
        RateLimiter::hit($perMinuteKey, 60); // window 60 detik

        // --- 3. Per-nomor, per jam ---
        $perHourKey = 'wa_ratelimit_hour:' . $phone;
        if (RateLimiter::tooManyAttempts($perHourKey, 20)) {
            $seconds = RateLimiter::availableIn($perHourKey);
            $minutes = ceil($seconds / 60);
            Log::info('Per-hour rate limit', ['phone' => $this->maskPhone($phone)]);
            return $this->rateLimitResponse(
                "Batas permintaan per jam tercapai. Coba lagi dalam {$minutes} menit."
            );
        }
        RateLimiter::hit($perHourKey, 3600); // window 1 jam

        return $next($request);
    }

    private function rateLimitResponse(string $message): Response
    {
        return response()->json([
            'success'      => false,
            'rate_limited' => true,
            'message'      => $message,
        ], 429);
    }

    private function maskPhone(string $phone): string
    {
        // 628123456789 → 6281****6789
        if (strlen($phone) > 8) {
            return substr($phone, 0, 4) . '****' . substr($phone, -4);
        }
        return '****';
    }
}