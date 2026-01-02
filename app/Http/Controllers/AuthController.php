<?php

namespace App\Http\Controllers;

use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Generate token untuk frontend (tanpa login)
     * Endpoint ini untuk mendapatkan token awal
     */
    public function generateToken(Request $request): JsonResponse
    {
        try {
            $origin = null;

            // ===============================
            // 1️⃣ INTERNAL REQUEST (Next.js, Server)
            // ===============================
            if ($request->hasHeader('X-SIGNATURE')) {
                // Gunakan host sebagai identitas
                $origin =
                    $request->header('x-forwarded-host') ??
                    $request->header('host');
            }
            // ===============================
            // 2️⃣ BROWSER REQUEST
            // ===============================
            else {
                $origin = $request->header('origin');

                $allowedOrigins = config('cors.allowed_origins');

                if (
                    !$origin ||
                    (!in_array($origin, $allowedOrigins) && !in_array('*', $allowedOrigins))
                ) {
                    return $this->forbidden('Origin not allowed');
                }
            }

            // ===============================
            // 3️⃣ GENERATE TOKEN
            // ===============================
            $tokens = $this->jwtService->generateTokens([
                'client_type' => 'frontend',
                'origin' => $origin,
            ]);

            

            // return cookie
           return response()
                ->json([
                    'success' => true,
                    'message' => 'Authenticated',
                ])
                ->cookie(
                    'access_token',
                    $tokens['access_token'],
                    60,        // menit
                    '/',
                    null,
                    true,      // Secure (WAJIB untuk SameSite=None)
                    true,      // HttpOnly
                    false,
                    'None'     // 🔥 WAJIB
                )
                ->cookie(
                    'refresh_token',
                    $tokens['refresh_token'],
                    43200,
                    '/',
                    null,
                    true,
                    true,
                    false,
                    'None'
                );


        } catch (\Exception $e) {
            return $this->serverError(
                'Failed to generate token: ' . $e->getMessage()
            );
        }
    }


    /**
     * Refresh access token menggunakan refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return $this->badRequest('Refresh token is required');
        }

        try {
            $tokens = $this->jwtService->refreshAccessToken($refreshToken);

            if (!$tokens) {
                return $this->unauthorized('Invalid or expired refresh token');
            }

            return $this->success($tokens, 'Token refreshed successfully');

        } catch (\Exception $e) {
            return $this->serverError('Failed to refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Revoke refresh token (logout)
     */
    public function revoke(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return $this->badRequest('Refresh token is required');
        }

        try {
            $this->jwtService->revokeRefreshToken($refreshToken);
            return $this->success(null, 'Token revoked successfully');

        } catch (\Exception $e) {
            return $this->serverError('Failed to revoke token: ' . $e->getMessage());
        }
    }

    /**
     * Validate token
     */
    public function validateToken(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->badRequest('Token is required');
        }

        $isValid = $this->jwtService->validateAccessToken($token);

        if ($isValid) {
            $payload = $this->jwtService->getPayload($token);
            return $this->success([
                'valid' => true,
                'payload' => $payload,
            ]);
        }

        return $this->unauthorized('Invalid token');
    }
}
