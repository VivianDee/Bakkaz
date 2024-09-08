<?php

use App\Helpers\ResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //Register middlewears
        $middleware->alias([
           'VerifyApiKey' => \App\Http\Middleware\VerifyApiKey::class,
           'VerifyWebhookSignature' => \App\Http\Middleware\VerifyWebhookSignature::class,
       ]);
   })
   ->withExceptions(function (Exceptions $exceptions) {
       //Register exceptions
       $exceptions->render(function (NotFoundHttpException $e, Request $request) {
           return ResponseHelpers::notFound(message: 'Invalid Route');
       });

       $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
           return ResponseHelpers::error(message: 'Method not allowed for api route');
       });
   })->create();
