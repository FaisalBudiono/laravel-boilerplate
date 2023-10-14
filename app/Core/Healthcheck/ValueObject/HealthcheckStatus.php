<?php

namespace App\Core\Healthcheck\ValueObject;

readonly class HealthcheckStatus
{
    public function __construct(
        public string $name,
        public ?\Throwable $error,
    ) {
    }
}
