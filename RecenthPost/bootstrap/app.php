<?php
namespace App\Services;

use App\Helpers\ResponseHelpers;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use GuzzleHttp\Exception\RequestException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        api: __DIR__ . "/../routes/api.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up"
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            "VerifyApiKey" => \App\Http\Middleware\VerifyApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            return ResponseHelpers::notFound(message: $e->getMessage() . "");
        });

        $exceptions->render(function (
            MethodNotAllowedHttpException $e,
            Request $request
        ) {
            return ResponseHelpers::error(
                message: "Method not allowed for api route"
            );
        });
        $exceptions->render(function (
            ConnectionException $e,
            Request $request
        ) {
            return ResponseHelpers::error(
                message: "Network Error please check you and try "
            );
        });

        $exceptions->render(function (RequestException $e, Request $request) {
            return ResponseHelpers::error(
                message: "Network Error please check you and try "
            );
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            return ResponseHelpers::error(message: "{$e->getMessage()}");
        });

        $exceptions->render(function (
            RouteNotFoundException $e,
            Request $request
        ) {
            return ResponseHelpers::unauthorized();
        });
    })

    ->create();
