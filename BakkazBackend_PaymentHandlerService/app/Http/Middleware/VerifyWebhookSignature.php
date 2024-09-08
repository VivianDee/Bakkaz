<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('X-Paystack-Signature', null)) {
            Log::error(message: "Webhook Access Denied: Invalid Signature");
            return ResponseHelpers::unauthorized(message: "Webhook Access Denied");
        }

        $input = $request->getContent();

        $secretKey = env('PAYSTACK_SECRET_KEY');

        $signature = $request->header('X-Paystack-Signature');

        if ($signature !== hash_hmac('sha512', $input, $secretKey)) {
            
            Log::error(message: "Webhook Access Denied: Invalid Signature");

            return ResponseHelpers::unauthorized(message: "Webhook Access Denied");
        }

        return $next($request);
    }
}
