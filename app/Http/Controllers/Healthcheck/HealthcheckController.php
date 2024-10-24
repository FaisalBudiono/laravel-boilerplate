<?php

declare(strict_types=1);

namespace App\Http\Controllers\Healthcheck;

use App\Exceptions\Http\InternalServerError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Healthcheck\HealthcheckRequest;
use App\Services\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Services\Healthcheck\HealthcheckServiceContract;
use App\Services\Logger\Logger;
use App\Services\Logger\Message\Enum\ProcessingStatus;
use App\Services\Logger\Message\LogMessageDirectorContract;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HealthcheckController extends Controller
{
    public function __construct(
        protected HealthcheckServiceContract $service,
        protected LogMessageDirectorContract $logDirector,
        protected Logger $logger,
    ) {}

    public function index(HealthcheckRequest $req): JsonResponse
    {
        try {
            $this->logger->info(
                $this->logDirector->buildHTTP(
                    $this->logDirector->makeBuilder(),
                    ProcessingStatus::BEGIN,
                )->message('Healthcheck endpoint')
                    ->build(),
            );

            $status = $this->service->getHealthiness($req);

            if ($status->isHealthy()) {
                $this->logger->info(
                    $this->logDirector->buildHTTP(
                        $this->logDirector->makeBuilder(),
                        ProcessingStatus::SUCCESS,
                    )->message('Healthcheck endpoint')
                        ->build(),
                );

                return response()
                    ->json($status->toArray())
                    ->setStatusCode(Response::HTTP_OK);
            }

            $this->logger->emergency(
                $this->logDirector->buildHTTP(
                    $this->logDirector->makeBuilder(),
                    ProcessingStatus::SUCCESS,
                )->message('Healthcheck endpoint')
                    ->meta([
                        'detail' => $status->toArrayDetail(),
                    ])->build(),
            );

            return response()
                ->json($status->toArray())
                ->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Throwable $e) {
            $this->logger->error(
                $this->logDirector->buildForException(
                    $this->logDirector->buildHTTP(
                        $this->logDirector->makeBuilder(),
                        ProcessingStatus::ERROR,
                    ),
                    $e,
                )->build(),
            );

            throw new InternalServerError(new ExceptionMessageGeneric(), $e);
        }
    }
}
