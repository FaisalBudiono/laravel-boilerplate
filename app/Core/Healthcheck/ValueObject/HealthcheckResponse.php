<?php

namespace App\Core\Healthcheck\ValueObject;

use Illuminate\Contracts\Support\Arrayable;

readonly class HealthcheckResponse implements Arrayable
{
    public function __construct(
        public string $version,
    ) {
    }

    public function toArray()
    {
        return [
            'version' => $this->version,
        ];
    }
}
