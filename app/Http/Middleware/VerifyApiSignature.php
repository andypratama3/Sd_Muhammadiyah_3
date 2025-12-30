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
        $secret = env('API_SECRET_KEY');


        if (!$secret) {
            return response()->json([
                'message' => 'Server misconfiguration'
            ], 500);
        }

        /* =====================================================
         * 1️⃣ Required security headers
         * ===================================================== */
        $timestamp = $request->header('X-TIMESTAMP');
        $nonce     = $request->header('X-NONCE');
        $signature = $request->header('X-SIGNATURE');
        $clientIp  = $request->header('X-CLIENT-IP');

        if (!$timestamp || !$nonce || !$signature || !$clientIp) {
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
         * 3️⃣ Resolve REAL client IP (Cloudflare / Proxy safe)
         * ===================================================== */
        $realIp =
            $request->header('cf-connecting-ip') ??
            $request->header('x-forwarded-for') ??
            $request->ip();

        $realIp = trim(explode(',', $realIp)[0]);


        /* =====================================================
         * 4️⃣ Signature validation (HMAC SHA256)
         * SIGN STRING FORMAT MUST MATCH FRONTEND
         * ===================================================== */
        $stringToSign = "{$timestamp}.{$nonce}.{$clientIp}";
        $expected     = hash_hmac('sha256', $stringToSign, $secret);

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

        Cache::put($nonceKey, true, 120); // 2 minutes TTL

        /* =====================================================
         * 6️⃣ Rate limiting (Atomic, race-safe)
         * ===================================================== */
        $rateKey = "rate:{$clientIp}";
        $limit   = 60; // requests per minute

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
