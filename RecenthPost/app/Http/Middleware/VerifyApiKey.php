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
        $apiKey = $request->header("x-api-key");
        $fromEnv = "d7J\$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU";

        if ($apiKey !== $fromEnv) {
            Log::error(message: "Unauthorized Request: Invalid API-KEY");
            return ResponseHelpers::unauthorized();
        }

        return $next($request);
    }
}
