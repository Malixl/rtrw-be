<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\forceJSONResponse;
use App\Http\Middleware\GzipResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->append(InitializeTenancyByDomain::class);
        // $middleware->append(PreventAccessFromCentralDomains::class);
        $middleware->append(HandleCors::class);
        $middleware->append(forceJSONResponse::class);
        // Gzip compression untuk semua response (khususnya GeoJSON)
        $middleware->append(GzipResponse::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'check.role' => CheckRole::class,
            'check.permission' => CheckPermission::class,
            'gzip' => GzipResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'code' => 401,
                    'status' => false,
                    'message' => 'Akses tidak diizinkan. Silakan masuk terlebih dahulu.',
                ], 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Route not found',
                ], 404);
            }
        });
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('auth:clear-resets')->hourly();
    })->create();
