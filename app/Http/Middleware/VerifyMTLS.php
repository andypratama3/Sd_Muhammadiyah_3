<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyMTLS
{
    public function handle(Request $request, Closure $next)
    {
        // Only verify for webhook endpoint
        if ($request->path() === 'api/v1/webhook/whatsapp') {
            
            // DEBUG: Log all SSL-related headers
            \Log::info('=== mTLS DEBUG ===', [
                'SSL_CLIENT_CERT' => $request->server('SSL_CLIENT_CERT') ?? 'NOT FOUND',
                'SSL_CLIENT_VERIFY' => $request->server('SSL_CLIENT_VERIFY') ?? 'NOT FOUND',
                'SSL_PROTOCOL' => $request->server('SSL_PROTOCOL') ?? 'NOT FOUND',
                'HTTPS' => $request->server('HTTPS') ?? 'NOT SET',
                'All headers' => $request->headers->all(),
            ]);
            
            // Check if SSL_CLIENT_CERT header exists
            $clientCert = $request->server('SSL_CLIENT_CERT');
            
            if (!$clientCert) {
                return response()->json([
                    'error' => 'Client certificate required',
                    'message' => 'mTLS certificate is mandatory for this endpoint'
                ], 403);
            }
            
            // Check if certificate was verified successfully
            $verifyResult = $request->server('SSL_CLIENT_VERIFY');
            
            if ($verifyResult !== 'SUCCESS') {
                return response()->json([
                    'error' => 'Certificate verification failed',
                    'verify_result' => $verifyResult ?? 'NONE'
                ], 403);
            }
        }
        
        return $next($request);
    }
}