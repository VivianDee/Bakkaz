<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\CurlTimeoutException;

class CurlMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), "cURL error 28") !== false) {
                throw new CurlTimeoutException($e->getMessage());
            }

            throw $e; // Rethrow the exception if it's not a cURL timeout error
        }
    }
}
