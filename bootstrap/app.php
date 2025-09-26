<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException; // http status code

// use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
// use Illuminate\Routing\Exceptions\InvalidSignatureException;
// use Illuminate\Foundation\Http\Middleware\TrimStrings;
// use Illuminate\Http\Exceptions\HttpResponseException;
// use Illuminate\Routing\Middleware\SubstituteBindings;
// use Illuminate\Http\Middleware\SetCacheHeaders;
// use Illuminate\Validation\ValidationException;
// use Illuminate\Http\Middleware\TrustProxies;
// use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwt.auth' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
            'jwt.refresh' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\RefreshToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            $statusCode = $e instanceof HttpException
                ? $e->getStatusCode()
                : 500; // default 500

            return response()->json([
                'status' => false,
                'statusCode' => $statusCode,
                'message' => $e->getMessage() ?: 'Server Error',
            ], $statusCode);
        });
        // $exceptions->render(function (HttpException $e) {
        //     return response()->json([
        //         'message' => $e->getMessage(), 
        //         'statusCode'=>$e->getStatusCode()
        //     ], $e->getStatusCode());
        // });
        // $exceptions->render(function (HttpException $e) {
        //     return response()->json([
        //         'status' => $e->getCode(),
        //         'statusCode' => $e->getCode(),
        //         'message' => $e->getMessage(),
        //     ]);
        // });
    })->create();
