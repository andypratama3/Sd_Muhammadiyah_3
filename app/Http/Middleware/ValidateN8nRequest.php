<?php
// app/Http/Middleware/ValidateN8nRequest.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ValidateN8nRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $secretKey = $request->header('X-N8N-Secret');

        if (!$secretKey || $secretKey !== config('app.n8n_secret_key')) {
            Log::warning('Unauthorized API access attempt', [
                'ip'     => $request->ip(),
                'path'   => $request->path(),
                'header' => $request->header('X-N8N-Secret') ? 'present_but_wrong' : 'missing',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}