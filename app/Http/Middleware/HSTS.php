<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HSTS
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Check if the response is not a BinaryFileResponse
        if (! $response instanceof BinaryFileResponse) {
            // Add the Strict-Transport-Security header
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubdomains');
        }

        return $response;
    }

}
