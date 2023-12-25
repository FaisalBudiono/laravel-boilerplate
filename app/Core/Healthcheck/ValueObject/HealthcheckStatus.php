<?php

declare(strict_types=1);

namespace App\Core\Healthcheck\ValueObject;

readonly class HealthcheckStatus
{
    public function __construct(
        public string $name,
        public ?\Throwable $error,
    ) {
    }
}
