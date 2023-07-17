<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Healthcheck\HealthcheckCore;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\Healthchecker\HealthcheckerMysql;
use App\Core\Healthcheck\Healthchecker\HealthcheckerMysqlContract;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;
use App\Core\Healthcheck\VersionFetcher\VersionFetcherConfig;
use Tests\Unit\Providers\CoreBinder\Dependencies\DependencyFactoryMockery;

class CoreBinderTestCaseHealthcheck extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            HealthcheckCoreContract::class => [
                HealthcheckCore::class,
                [
                    new DependencyFactoryMockery($this->test, VersionFetcher::class),
                    new DependencyFactoryMockery($this->test, HealthcheckerMysqlContract::class),
                ],
            ],
            HealthcheckerMysqlContract::class => [
                HealthcheckerMysql::class,
            ],
            VersionFetcher::class => [
                VersionFetcherConfig::class,
            ],
        ];
    }
}
