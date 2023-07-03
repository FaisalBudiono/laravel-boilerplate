<?php

namespace App\Core\Healthcheck;

use App\Core\Healthcheck\Healthchecker\HealthcheckerMysqlContract;
use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;
use App\Port\Core\Healthcheck\GetHealthcheckPort;

class HealthcheckCore implements HealthcheckCoreContract
{
    public function __construct(
        protected VersionFetcher $versionFetcher,
        protected HealthcheckerMysqlContract $healthcheckerMysql,
    ) {
    }

    public function getHealthiness(GetHealthcheckPort $request): HealthcheckResponse
    {
        return new HealthcheckResponse(
            $this->versionFetcher->fullVersion(),
            $this->healthcheckerMysql->getStatus(),
        );
    }
}
