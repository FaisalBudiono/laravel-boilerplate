<?php

declare(strict_types=1);

namespace App\Services\Healthcheck\Checker;

use App\Services\Healthcheck\ValueObject\HealthcheckStatus;
use Illuminate\Support\Facades\DB;

class HealthcheckerPostgres implements HealthcheckerPostgresContract
{
    public function getStatus(): HealthcheckStatus
    {
        try {
            DB::connection('pgsql')->getPdo();

            return $this->makeStatus(null);
        } catch (\Throwable $e) {
            return $this->makeStatus($e);
        }
    }

    protected function makeStatus(?\Throwable $e): HealthcheckStatus
    {
        return new HealthcheckStatus(
            'postgres',
            $e,
        );
    }
}
