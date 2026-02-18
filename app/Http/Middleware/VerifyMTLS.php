<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyMTLS
{
    public function handle(Request $request, Closure $next)
    {
        // Check if this is webhook endpoint
        if ($request->path() === 'api/v1/webhook/whatsapp') {
            // Verify client certificate exists
            $clientCert = $request->server('SSL_CLIENT_CERT');
            
            if (!$clientCert) {
                return response()->json([
                    'error' => 'Client certificate required'
                ], 403);
            }
            
            // Verify certificate is valid
            $verifyResult = $request->server('SSL_CLIENT_VERIFY');
            
            if ($verifyResult !== 'SUCCESS') {
                return response()->json([
                    'error' => 'Client certificate verification failed'
                ], 403);
            }
        }
        
        return $next($request);
    }
}