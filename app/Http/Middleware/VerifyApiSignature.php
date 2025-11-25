<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VerifyApiSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = env('API_SECRET_KEY');
        $timestamp = $request->header('X-TIMESTAMP');
        $signature = $request->header('X-SIGNATURE');

        if (!$timestamp || !$signature) {
            return response()->json(['error' => 'Unauthorized: missing headers'], 401);
        }

        try {
            $requestTime = new Carbon($timestamp);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid timestamp'], 401);
        }

        if (abs(time() - $requestTime->timestamp) > 120) {
            return response()->json(['error' => 'Request expired'], 401);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
