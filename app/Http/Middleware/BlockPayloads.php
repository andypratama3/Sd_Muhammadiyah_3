<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockPayloads
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Example: Block requests with specific payloads
        $blockedPayloads = [
            'bad_payload',
            'another_bad_payload',
            // Add more payloads or patterns here
        ];

        $requestPayload = $request->all();

        foreach ($blockedPayloads as $payload) {
            if (in_array($payload, $requestPayload)) {
                return response()->json(['error' => 'Payload blocked'], 404);
            }
        }

        return $next($request);
    }
}
