<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\Healthcheck\HealthcheckCore;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\Healthchecker\HealthcheckerMysql;
use App\Core\Healthcheck\Healthchecker\HealthcheckerMysqlContract;
use App\Core\Healthcheck\Healthchecker\HealthcheckerRedis;
use App\Core\Healthcheck\Healthchecker\HealthcheckerRedisContract;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;
use App\Core\Healthcheck\VersionFetcher\VersionFetcherConfig;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderHealthcheck implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(HealthcheckCoreContract::class, function (Application $app) {
            return new HealthcheckCore(
                $app->make(VersionFetcher::class),
                $app->make(HealthcheckerMysqlContract::class),
                $app->make(HealthcheckerRedisContract::class),
            );
        });

        $app->bind(HealthcheckerMysqlContract::class, function (Application $app) {
            return new HealthcheckerMysql();
        });

        $app->bind(HealthcheckerRedisContract::class, function (Application $app) {
            return new HealthcheckerRedis();
        });

        $app->bind(VersionFetcher::class, function (Application $app) {
            return new VersionFetcherConfig();
        });
    }
}
