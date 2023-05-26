<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Healthcheck\HealthcheckCore;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;
use App\Core\Healthcheck\VersionFetcher\VersionFetcherConfig;

class CoreBinderTestCaseHealthcheck extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            HealthcheckCoreContract::class => [
                HealthcheckCore::class,
                [
                    VersionFetcher::class,
                ],
            ],
            VersionFetcher::class => [
                VersionFetcherConfig::class,
            ],
        ];
    }
}
