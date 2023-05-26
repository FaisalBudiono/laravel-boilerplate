<?php

namespace App\Core\Healthcheck;

use App\Core\Healthcheck\ValueObject\HealthcheckResponse;

interface HealthcheckCoreContract
{
    public function getHealthiness(): HealthcheckResponse;
}
