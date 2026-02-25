<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeadersMiddleware
{
    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];

    public function handle($request, Closure $next)
    {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);

        $response = $next($request);
        $origin = $request->headers->get('Origin');

        $allowedOrigins = [
            env('FRONTEND_URL'),
            'https://sdmuhammadiyah3smd.com',
            'https://www.sdmuhammadiyah3smd.com',
        ];

        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // ✅ Permissions-Policy
        $response->headers->set('Permissions-Policy', 
            'geolocation=(self), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), accelerometer=()'
        );

        // ✅ FIXED CSP - includes FontAwesome CDN correctly + Nominatim + unpkg connect-src
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
                "cdn.jsdelivr.net cdnjs.cloudflare.com code.jquery.com ajax.googleapis.com cdn.bootcdn.net " .
                "kit.fontawesome.com ka-f.fontawesome.com buttons.github.io cdn.datatables.net " .
                "cdn.quilljs.com unpkg.com https://www.googletagmanager.com https://www.google-analytics.com; " .
            "style-src 'self' 'unsafe-inline' " .
                "cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com unpkg.com " .
                "cdn.datatables.net cdn.quilljs.com kit.fontawesome.com ka-f.fontawesome.com; " .
            "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net cdnjs.cloudflare.com kit.fontawesome.com ka-f.fontawesome.com; " .
            "img-src 'self' data: blob: https: *.fontawesome.com *.ka-f.fontawesome.com; " .
            "frame-src 'self' https://www.youtube.com https://app.midtrans.com https://app.sandbox.midtrans.com; " .
            "connect-src 'self' https://app.midtrans.com https://app.sandbox.midtrans.com " .
                "https://www.googletagmanager.com https://www.google-analytics.com " .
                "https://kit.fontawesome.com https://ka-f.fontawesome.com " .
                "https://nominatim.openstreetmap.org https://unpkg.com; " .
            "object-src 'none'; " .
            "base-uri 'self'; " .
            "form-action 'self';"
        );

        return $response;
    }

    private function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header) {
            header_remove($header);
        }
    }
}