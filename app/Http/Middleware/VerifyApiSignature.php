<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        /* =====================================================
         * 0️⃣ Secret validation
         * ===================================================== */
        $secretHex = config('services.api.secret');
        // \Log::info('SECRET CHECK', [
        //     'secret' => config('services.api.secret')
        // ]);


        if (!$secretHex) {
            return response()->json([
                'message' => 'Server misconfiguration'
            ], 500);
        }

        /* =====================================================
         * 1️⃣ Required headers
         * ===================================================== */
        $timestamp = $request->header('X-TIMESTAMP');
        $nonce     = $request->header('X-NONCE');
        $signature = $request->header('X-SIGNATURE');

        if (!$timestamp || !$nonce || !$signature) {
            return response()->json([
                'message' => 'Missing security headers'
            ], 401);
        }

        /* =====================================================
         * 2️⃣ Timestamp validation (±120 seconds)
         * ===================================================== */
        if (!ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 120) {
            return response()->json([
                'message' => 'Request expired'
            ], 401);
        }

        /* =====================================================
         * 3️⃣ Resolve REAL client IP (Proxy safe)
         * ===================================================== */
        $clientIp =
            $request->header('cf-connecting-ip') ??
            $request->header('x-forwarded-for') ??
            $request->ip();

        $clientIp = trim(explode(',', $clientIp)[0]);

        /* =====================================================
         * 4️⃣ Signature validation (HMAC SHA256)
         * STRING FORMAT HARUS IDENTIK DENGAN FRONTEND
         * ===================================================== */
        $stringToSign = "{$timestamp}.{$nonce}";
        $secret = hex2bin($secretHex);

        $expected = hash_hmac(
            'sha256',
            $stringToSign,
            $secret
        );

        if (!hash_equals($expected, $signature)) {
            return response()->json([
                'message' => 'Invalid signature'
            ], 401);
        }

        /* =====================================================
         * 🧪 LOCAL ENV → skip heavy protection
         * ===================================================== */
        if (app()->environment('local')) {
            return $next($request);
        }

        /* =====================================================
         * 5️⃣ Anti-Replay (Nonce per IP)
         * ===================================================== */
        $nonceKey = "nonce:{$clientIp}:{$nonce}";

        if (Cache::has($nonceKey)) {
            return response()->json([
                'message' => 'Replay detected'
            ], 401);
        }

        Cache::put($nonceKey, true, 120);

        /* =====================================================
         * 6️⃣ Rate limiting (Atomic & race-safe)
         * ===================================================== */
        $rateKey = "rate:{$clientIp}";
        $limit   = 60;

        $count = Cache::increment($rateKey);

        if ($count === 1) {
            Cache::put($rateKey, 1, 60);
        }

        if ($count > $limit) {
            return response()->json([
                'message' => 'Too many requests'
            ], 429);
        }

        return $next($request);
    }
}
