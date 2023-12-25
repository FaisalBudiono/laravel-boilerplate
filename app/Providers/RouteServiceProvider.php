<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\Healthcheck\HealthcheckController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    protected $modelBindings = [
        \App\Providers\ModelBinding\ModelBindingPost::class,
        \App\Providers\ModelBinding\ModelBindingUser::class,
    ];


    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureModelBindings();
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->createHealthcheckEndpoint();

            Route::middleware('api')
                ->group(base_path('routes/api.php'));
        });
    }

    protected function configureModelBindings(): void
    {
        foreach ($this->modelBindings as $binding) {
            /** @var \App\Providers\ModelBinding\ModelBinding */
            $bindingClass = new $binding();

            $bindingClass->bindModel();
            $bindingClass->registerPattern();
        }
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute($this->getThrottlePerMinute())
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function createHealthcheckEndpoint(): void
    {
        Route::get('', [HealthcheckController::class, 'index'])->name('healthcheck');
    }

    protected function getThrottlePerMinute(): int
    {
        return intval(config('api.throttle'));
    }
}
