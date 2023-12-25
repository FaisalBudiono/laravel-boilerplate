<?php

declare(strict_types=1);

namespace App\Core\Healthcheck;

use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Port\Core\Healthcheck\GetHealthcheckPort;

interface HealthcheckCoreContract
{
    public function getHealthiness(GetHealthcheckPort $request): HealthcheckResponse;
}
