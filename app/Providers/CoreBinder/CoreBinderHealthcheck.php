<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Services\Healthcheck\Checker\HealthcheckerPostgres;
use App\Services\Healthcheck\Checker\HealthcheckerPostgresContract;
use App\Services\Healthcheck\Checker\HealthcheckerRedis;
use App\Services\Healthcheck\Checker\HealthcheckerRedisContract;
use App\Services\Healthcheck\HealthcheckService;
use App\Services\Healthcheck\HealthcheckServiceContract;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderHealthcheck implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            HealthcheckerPostgresContract::class,
            fn (Application $app) => new HealthcheckerPostgres(),
        );

        $app->bind(
            HealthcheckerRedisContract::class,
            fn (Application $app) => new HealthcheckerRedis(),
        );

        $app->bind(
            HealthcheckServiceContract::class,
            fn (Application $app) => new HealthcheckService(
                checkerPostgres: $app->make(HealthcheckerPostgresContract::class),
                checkerRedis: $app->make(HealthcheckerRedisContract::class),
            ),
        );
    }
}
