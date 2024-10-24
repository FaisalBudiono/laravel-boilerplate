<?php

declare(strict_types=1);

namespace App\Services\Healthcheck\Checker;

use App\Services\Healthcheck\ValueObject\HealthcheckStatus;
use Illuminate\Support\Facades\Redis;

class HealthcheckerRedis implements HealthcheckerRedisContract
{
    public function getStatus(): HealthcheckStatus
    {
        try {
            Redis::client();

            return $this->makeStatus(null);
        } catch (\Throwable $e) {
            return $this->makeStatus($e);
        }
    }

    protected function makeStatus(?\Throwable $e): HealthcheckStatus
    {
        return new HealthcheckStatus(
            'redis',
            $e,
        );
    }
}
