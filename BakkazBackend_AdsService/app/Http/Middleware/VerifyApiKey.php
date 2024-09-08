<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelpers;
use Illuminate\Support\Facades\Log;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if ($apiKey !== env('API_KEY')) {

            Log::error(message: "Unauthorized Request: Invalid route");

            return ResponseHelpers::unauthorized();
        }

        return $next($request);
    }
}
