<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelpers;
use Illuminate\Support\Facades\Log;
use App\Helpers\DateHelper;

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
        
        if ($apiKey) {
            if ($apiKey !== env("API_KEY")) {
                Log::error(
                    message: "Unauthorized Request: Invalid API-KEY [Date: [" .
                        DateHelper::now() .
                        "]"
                );
                return ResponseHelpers::unauthorized("Invalid API_KEY");
            }
        } else {
            return ResponseHelpers::unauthorized("API_KEY not found");
        }
        return $next($request);
    }
}
