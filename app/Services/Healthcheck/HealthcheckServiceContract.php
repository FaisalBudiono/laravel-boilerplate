<?php

declare(strict_types=1);

namespace App\Services\Healthcheck;

use App\Port\Services\Healthcheck\GetHealthcheckPort;
use App\Services\Healthcheck\ValueObject\HealthcheckResponse;

interface HealthcheckServiceContract
{
    public function getHealthiness(GetHealthcheckPort $request): HealthcheckResponse;
}
