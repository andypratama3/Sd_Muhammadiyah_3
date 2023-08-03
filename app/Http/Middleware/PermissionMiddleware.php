<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if ($request->user()->hasRole('superadmin')) {
            return $next($request);
        }
        if (! $request->user()->can($permission)) {
            abort(404);
        }
        return $next($request);
    }
}
