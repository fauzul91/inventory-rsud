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
            'access_token',
        ]);
        $middleware->validateCsrfTokens(except: [
            'telescope/*',
            'telescope-api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof ValidationException) {
                    return ResponseHelper::jsonResponse(false, 'Validasi gagal', $e->errors(), 422);
                }

                if ($e instanceof AuthenticationException) {
                    return ResponseHelper::jsonResponse(false, 'Sesi berakhir, silakan login kembali', null, 401);
                }

                if ($e instanceof UnauthorizedException) {
                    return ResponseHelper::jsonResponse(false, 'Anda tidak memiliki hak akses untuk fitur ini', null, 403);
                }
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return ResponseHelper::jsonResponse(false, 'Data tidak ditemukan', null, 404);
                }
                Log::error($e->getMessage(), [
                    'url' => $request->fullUrl(),
                    'exception' => get_class($e)
                ]);
                return ResponseHelper::jsonResponse(
                    false,
                    'Server Error: ' . (config('app.debug') ? $e->getMessage() : 'Internal Server Error'),
                    null,
                    500
                );
            }
        });
    })->create();
