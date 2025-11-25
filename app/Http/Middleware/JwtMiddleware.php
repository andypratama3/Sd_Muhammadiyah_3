<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token not provided'
            ], 401);
        }

        try {
            $secretKey = env('JWT_SECRET'); 
            $credentials = JWT::decode($token, new Key($secretKey, 'HS256'));

            // simpan data user dari token ke request
            $request->attributes->set('auth', $credentials);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token',
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}
