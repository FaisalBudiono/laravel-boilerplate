<?php

namespace App\Core\Healthcheck\ValueObject;

use Throwable;

readonly class HealthcheckStatus
{
    public function __construct(
        public string $name,
        public ?Throwable $error,
    ) {
    }
}
