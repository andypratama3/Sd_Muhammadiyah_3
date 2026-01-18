<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockOriginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('origin') ?? $request->header('referer');

        if ($origin && str_contains($origin, 'sdmuhammadiyah3smd.com')) {
            abort(404, 'Access forbidden');
        }

        return $next($request);
    }
}
