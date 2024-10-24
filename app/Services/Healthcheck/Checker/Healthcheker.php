<?php

declare(strict_types=1);

namespace App\Services\Healthcheck\Checker;

use App\Services\Healthcheck\ValueObject\HealthcheckStatus;

interface Healthcheker
{
    public function getStatus(): HealthcheckStatus;
}
