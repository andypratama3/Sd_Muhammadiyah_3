<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\ApiResponse;
use App\Services\JwtService;
use Illuminate\Http\Request;

class JwtMiddleware
{
    use ApiResponse;

    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken() ?? $request->cookie('access_token');

            // \Log::info('JWT DEBUG', [
            //     'has_bearer' => (bool)$request->bearerToken(),
            //     'has_cookie' => (bool)$request->cookie('access_token'),
            //     'all_cookies' => $request->cookies->all(),
            //     'token' => $token,
            // ]);

        // 2️⃣ Fallback ke Cookie (PENTING)
        if (!$token) {
            $token = $request->cookie('access_token');
        }

        if (!$token) {
            return $this->unauthorized('Token not provided');
        }

        if (!$this->jwtService->validateAccessToken($token)) {
            return $this->unauthorized('Invalid or expired token');
        }

        $payload = $this->jwtService->getPayload($token);

        if (!$payload) {
            return $this->unauthorized('Invalid token payload');
        }

        $request->merge([
            'token_payload' => $payload
        ]);

        return $next($request);
    }

}
