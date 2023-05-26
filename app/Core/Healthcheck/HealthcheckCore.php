<?php

namespace App\Core\Healthcheck;

use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;

class HealthcheckCore implements HealthcheckCoreContract
{
    public function __construct(
        protected VersionFetcher $versionFetcher,
    ) {
    }

    public function getHealthiness(): HealthcheckResponse
    {
        return new HealthcheckResponse(
            $this->versionFetcher->fullVersion(),
        );
    }
}
