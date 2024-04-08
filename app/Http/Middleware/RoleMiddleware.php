<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role, $permission = null)
    {
        if (! $request->user()->hasRole($role)) {
            abort(404);
        }else {
            return Request::session()->get('url.intended') ?? route('artikel.show');
        }
        if ($permission !== null && ! $request->user()->can($permission)) {
            abort(404);
        }

        return $next($request);
    }
}
