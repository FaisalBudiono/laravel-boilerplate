<?php

namespace App\Providers\CoreBinder;

use App\Core\Healthcheck\HealthcheckCore;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;
use App\Core\Healthcheck\VersionFetcher\VersionFetcherConfig;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderHealthcheck implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(HealthcheckCoreContract::class, function (Application $app) {
            return new HealthcheckCore($app->make(VersionFetcher::class));
        });

        $app->bind(VersionFetcher::class, function (Application $app) {
            return new VersionFetcherConfig;
        });
    }
}
