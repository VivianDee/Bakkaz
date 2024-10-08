<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set("Accept", "application/json");
        $request->headers->set("Content-Type", "application/json");
        
        return $next($request);
    }
}
