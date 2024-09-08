<?php

use App\Exceptions\CurlTimeoutException;
use App\Helpers\ResponseHelpers;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\CurlMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        api: __DIR__ . "/../routes/api.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->append(CorsMiddleware::class);
        $middleware->alias([
            "SetJsonResponse" => \App\Http\Middleware\SetJsonResponse::class,
            "abilities" =>
            \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            "ability" =>
            \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            "VerifyApiKey" => \App\Http\Middleware\VerifyApiKey::class,
            "CurlMiddleware" => \App\Http\Middleware\CurlMiddleware::class,
        ]);
        $middleware->append(HandleCors::class);
        $middleware->append(CurlMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (
            BadRequestHttpException $e,
            Request $request
        ) {
            return ResponseHelpers::error("Bad Request");
        });
        $exceptions->render(function (
            AccessDeniedHttpException $e,
            Request $request
        ) {
            return ResponseHelpers::unauthorized(
                "Access Denied - Invalid Token"
            );
        });
        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            return ResponseHelpers::notFound(message: "Invalid Route");
        });
        $exceptions->render(function (
            CurlTimeoutException $e,
            Request $request
        ) {
            return ResponseHelpers::notFound(message: "Invalid Route");
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
            RouteNotFoundException $e,
            Request $request
        ) {
            return ResponseHelpers::unauthorized();
        });
    })

    ->create();
