<?php

declare(strict_types=1);

namespace App\Http\Controllers\Healthcheck;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Healthcheck\HealthcheckRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class HealthcheckController extends Controller
{
    public function __construct(
        protected HealthcheckCoreContract $core,
        protected LogMessageDirectorContract $logDirector,
        protected LogMessageBuilderContract $logBuilder,
    ) {
    }

    public function index(HealthcheckRequest $request)
    {
        try {
            Log::info(
                $this->logDirector->buildBegin(
                    clone $this->logBuilder
                )->message('Healthcheck endpoint')
                    ->build()
            );

            $status = $this->core->getHealthiness($request);

            if ($status->isHealthy()) {
                Log::info(
                    $this->logDirector->buildSuccess(
                        clone $this->logBuilder
                    )->message('Healthcheck endpoint')
                        ->build()
                );

                return response()
                    ->json($status->toArray())
                    ->setStatusCode(Response::HTTP_OK);
            }

            Log::emergency(
                $this->logDirector->buildSuccess(
                    clone $this->logBuilder
                )->message('Healthcheck endpoint')
                    ->meta([
                        'detail' => $status->toArrayDetail(),
                    ])->build()
            );

            return response()
                ->json($status->toArray())
                ->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Throwable $e) {
            Log::error(
                $this->logDirector->buildForException(
                    $this->logDirector->buildError(
                        clone $this->logBuilder
                    ),
                    $e,
                )->build()
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric());
        }
    }
}
