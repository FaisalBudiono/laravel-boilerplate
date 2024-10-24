<?php

declare(strict_types=1);

namespace App\Services\Healthcheck;

use App\Port\Services\Healthcheck\GetHealthcheckPort;
use App\Services\Healthcheck\Checker\HealthcheckerPostgresContract;
use App\Services\Healthcheck\Checker\HealthcheckerRedisContract;
use App\Services\Healthcheck\ValueObject\HealthcheckResponse;

class HealthcheckService implements HealthcheckServiceContract
{
    public function __construct(
        protected HealthcheckerPostgresContract $checkerPostgres,
        protected HealthcheckerRedisContract $checkerRedis,
    ) {}

    public function getHealthiness(GetHealthcheckPort $request): HealthcheckResponse
    {
        return new HealthcheckResponse(
            '0.0.0',
            $this->checkerPostgres->getStatus(),
            $this->checkerRedis->getStatus(),
        );
    }
}
