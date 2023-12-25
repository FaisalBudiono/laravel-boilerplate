<?php

declare(strict_types=1);

namespace App\Core\Healthcheck\Healthchecker;

use App\Core\Healthcheck\ValueObject\HealthcheckStatus;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthcheckerMysql implements HealthcheckerMysqlContract
{
    public function getStatus(): HealthcheckStatus
    {
        try {
            DB::connection('mysql')->getPdo();

            return $this->makeStatus(null);
        } catch (\Throwable $e) {
            return $this->makeStatus($e);
        }
    }

    protected function makeStatus(?Throwable $e): HealthcheckStatus
    {
        return new HealthcheckStatus(
            'mysql',
            $e,
        );
    }
}
