<?php

namespace App\Core\Healthcheck\Healthchecker;

use App\Core\Healthcheck\ValueObject\HealthcheckStatus;
use Illuminate\Support\Facades\Redis;
use Throwable;

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

    protected function makeStatus(?Throwable $e): HealthcheckStatus
    {
        return new HealthcheckStatus(
            'redis',
            $e,
        );
    }
}
