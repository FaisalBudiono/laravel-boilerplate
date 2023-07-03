<?php

namespace App\Core\Healthcheck\Healthchecker;

use App\Core\Healthcheck\ValueObject\HealthcheckStatus;

interface Healthchecker
{
    public function getStatus(): HealthcheckStatus;
}
