<?php

namespace App\Http\Controllers;

use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Generate token (initial login-less token)
     */
    public function generateToken(Request $request): JsonResponse
    {
        try {
            $origin = $request->header('Origin');
            $allowedOrigins = config('cors.allowed_origins', []);

            if (!$origin || (!in_array($origin, $allowedOrigins) && !in_array('*', $allowedOrigins))) {
                return response()->json(['success' => false, 'message' => 'Origin not allowed'], 403);
            }

            $tokens = $this->jwtService->generateTokens([
                'client_type' => 'frontend',
                'origin' => $origin,
            ]);

            // Set HTTP-only cookies
            $cookieAccess = Cookie::make(
                'access_token',
                $tokens['access_token'],
                $tokens['expires_in'] / 60, // menit
                '/',
                null,
                config('app.env') === 'production', // secure
                true, // httpOnly
                false,
                'Strict'
            );

            $cookieRefresh = Cookie::make(
                'refresh_token',
                $tokens['refresh_token'],
                ($tokens['refresh_expires_in'] ?? 604800) / 60, // menit
                '/',
                null,
                config('app.env') === 'production',
                true,
                false,
                'Strict'
            );

            return response()->json([
                'success' => true,
                'message' => 'Token generated successfully',
            ])->withCookie($cookieAccess)
              ->withCookie($cookieRefresh);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['success' => false, 'message' => 'Refresh token required'], 400);
        }

        try {
            $tokens = $this->jwtService->refreshAccessToken($refreshToken);

            if (!$tokens) {
                return response()->json(['success' => false, 'message' => 'Invalid refresh token'], 401);
            }

            // Update cookies
            $cookieAccess = Cookie::make(
                'access_token',
                $tokens['access_token'],
                $tokens['expires_in'] / 60,
                '/',
                null,
                config('app.env') === 'production',
                true,
                false,
                'Strict'
            );

            return response()->json(['success' => true, 'message' => 'Token refreshed successfully'])
                ->withCookie($cookieAccess);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revoke tokens
     */
    public function revoke(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token');

        try {
            if ($refreshToken) {
                $this->jwtService->revokeRefreshToken($refreshToken);
            }

            // Delete cookies
            $cookieAccess = Cookie::forget('access_token');
            $cookieRefresh = Cookie::forget('refresh_token');

            return response()->json(['success' => true, 'message' => 'Tokens revoked'])
                ->withCookie($cookieAccess)
                ->withCookie($cookieRefresh);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke tokens: '.$e->getMessage(),
            ], 500);
        }
    }
}
