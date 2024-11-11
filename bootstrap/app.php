<?php

use App\Jobs\FetchArticlesJob;
use Illuminate\Foundation\Application;
use App\Http\Middleware\ApiAuthenticate;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\CheckPasswordResetUser;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            \Illuminate\Http\Middleware\HandleCors::class,
            EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',
            SecurityHeaders::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => ApiAuthenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'check.password.reset' => CheckPasswordResetUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 401
            ], 401);
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new FetchArticlesJob)->hourly()->withoutOverlapping();
    })
    ->create();
