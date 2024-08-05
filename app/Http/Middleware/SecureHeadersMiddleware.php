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
        // Remove unwanted headers
        $this->removeUnwantedHeaders($this->unwantedHeaderList);

        // Proceed with the request
        $response = $next($request);

        // Set additional secure headers
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

              // Set Content-Security-Policy
              $response->headers->set('Content-Security-Policy', "default-src 'self'; style-src 'self';");
              $response->headers->set('Content-Security-Policy', "style-src 'self' unpkg.com cdn.datatables.net;");
              $response->headers->set('Content-Security-Policy', "style-src 'self' 'unsafe-inline' unpkg.com cdn.datatables.net cdn.jsdelivr.net cdnjs.cloudflare.com cdn.quilljs.com code.jquery.com fonts.googleapis.com https://use.fontawesome.com/releases/v5.15.4/css/all.css https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js https://fonts.gstatic.com/s/dmsans/v15/rP2Fp2ywxg089UriCZa4ET-DNl0.woff2 https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/fonts/bootstrap-icons.woff2 https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js;");

        return $response;
    }

    private function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header) {
            header_remove($header);
        }
    }
}
