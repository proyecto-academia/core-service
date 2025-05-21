<?php
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ForceJsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) { 
        $middleware->alias([
            'auth.remote' => \App\Http\Middleware\ValidateTokenWithAuthService::class,
            'auth.role' => \App\Http\Middleware\CheckUserRole::class,
        ]);
        $middleware->append([
            // Force JSON response
            ForceJsonResponse::class,
            \Illuminate\Http\Middleware\HandleCors::class,

        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Exception $e, Request $request) {
            if ($request->query('debug') == 'true') {
                dd($e);
            }

            $statusCode = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : ($e->status ?? 500);
            return response()->json([
                'success' => false,
                'route' => $request->path(),
                'verb' => $request->method(),
                'message' => $e->getMessage(),

            ], $statusCode);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            $statusCode = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : ($e->status ?? 500);
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'route' => $request->path(),
                    'verb' => $request->method(),
                    'message' => $e->getMessage(),
                ], $statusCode);
            }
        });
    })->create();


// use Illuminate\Foundation\Application;

// return Application::configure(basePath: dirname(__DIR__))
//     ->withRouting(
//         api: __DIR__.'/../routes/api.php',
//         apiPrefix: '',
//         commands: __DIR__.'/../routes/console.php',
//         health: '/up',
//     )
//     ->create();
