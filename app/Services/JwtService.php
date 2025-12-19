<?php

namespace App\Services;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class JwtService
{
    /**
     * Generate access token dan refresh token (TANPA USER)
     */
    public function generateTokens(array $payload = []): array
    {
        $accessToken = $this->encodeToken(
            array_merge($payload, [
                'type' => 'access',
            ]),
            config('jwt.ttl')
        );

        $refreshToken = $this->generateRefreshToken($payload);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_expires_in' => config('jwt.refresh_ttl') * 60,
        ];
    }

    /**
     * Encode JWT langsung (RESMI)
     */
    private function encodeToken(array $claims, int $ttlMinutes): string
    {
        $now = time();

        return JWTAuth::getJWTProvider()->encode(array_merge($claims, [
            'iat' => $now,
            'exp' => $now + ($ttlMinutes * 60),
            'iss' => config('app.url'),
            'sub' => 'frontend',
        ]));
    }

    /**
     * Generate refresh token dan simpan payload
     */
    private function generateRefreshToken(array $payload): string
    {
        $refreshToken = bin2hex(random_bytes(64));

        Cache::put(
            'refresh_token:' . $refreshToken,
            $payload,
            Carbon::now()->addMinutes(config('jwt.refresh_ttl'))
        );

        return $refreshToken;
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        $payload = Cache::get('refresh_token:' . $refreshToken);

        if (!$payload) {
            return null;
        }

        $accessToken = $this->encodeToken(
            array_merge($payload, [
                'type' => 'access',
            ]),
            config('jwt.ttl')
        );

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Validasi access token
     */
    public function validateAccessToken(string $token): bool
    {
        try {
            JWTAuth::setToken($token)->checkOrFail();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Ambil payload token
     */
    public function getPayload(string $token): ?array
    {
        try {
            return JWTAuth::setToken($token)->getPayload()->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Revoke refresh token
     */
    public function revokeRefreshToken(string $refreshToken): void
    {
        Cache::forget('refresh_token:' . $refreshToken);
    }
}
