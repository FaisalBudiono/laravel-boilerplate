<?php

declare(strict_types=1);

use App\Exceptions\Http\AbstractHttpException;
use App\Http\Controllers\Healthcheck\HealthcheckController;
use App\Http\Middleware\LoggingMiddleware;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        apiPrefix: '',
        api: __DIR__.'/../routes/api.php',
        then: function () {
            Route::get('/', [HealthcheckController::class, 'index'])->name('healthcheck');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(XRequestIDMiddleware::class);

        $middleware->api(
            prepend: [LoggingMiddleware::class],
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (AbstractHttpException $e) {
            return false;
        });
    })->create();
