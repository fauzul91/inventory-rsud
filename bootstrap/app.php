<?php

use App\Helpers\ResponseHelper;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api/api_v1.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
        $middleware->prependToGroup('api', \App\Http\Middleware\AuthenticateFromCookie::class);
        $middleware->statefulApi();
        $middleware->encryptCookies(except: [
            'access_token', // Tambahkan nama cookie Abang di sini
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                /** @var ValidationException $e */
                return ResponseHelper::jsonResponse(
                    false,
                    'Validasi gagal',
                    $e->errors(),
                    422
                );
            }

            if ($e instanceof AuthenticationException) {
                return ResponseHelper::jsonResponse(
                    false,
                    'Unauthenticated',
                    null,
                    401
                );
            }

            if ($e instanceof HttpExceptionInterface) {
                return ResponseHelper::jsonResponse(
                    false,
                    $e->getMessage() ?: 'Akses ditolak atau data tidak ditemukan',
                    null,
                    $e->getStatusCode()
                );
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ResponseHelper::jsonResponse(
                    false,
                    'Data tidak ditemukan di database',
                    null,
                    404
                );
            }

            Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return ResponseHelper::jsonResponse(
                false,
                'Terjadi kesalahan pada server: ' . (config('app.debug') ? $e->getMessage() : 'Internal Server Error'),
                null,
                500
            );
        });
    })->create();
