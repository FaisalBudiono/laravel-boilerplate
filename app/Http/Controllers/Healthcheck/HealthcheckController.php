<?php

namespace App\Http\Controllers\Healthcheck;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Healthcheck\HealthcheckRequest;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class HealthcheckController extends Controller
{
    public function __construct(
        protected HealthcheckCoreContract $core,
        protected LoggerMessageFormatterFactoryContract $loggerFormatter,
    ) {
    }

    public function index(HealthcheckRequest $request)
    {
        try {
            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::BEGIN,
                    'Healthcheck endpoint',
                    [
                        'input' => $request->toArray(),
                    ],
                )->getMessage()
            );

            $status = $this->core->getHealthiness($request);

            Log::info(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::SUCCESS,
                    'Healthcheck endpoint',
                    [],
                )->getMessage()
            );

            return response()
                ->json($status->toArray())
                ->setStatusCode(
                    $status->isHealthy()
                        ? Response::HTTP_OK
                        : Response::HTTP_INTERNAL_SERVER_ERROR
                );
        } catch (Exception $e) {
            Log::error(
                $this->loggerFormatter->makeGeneric(
                    $request->getEndpointInfo(),
                    $request->getXRequestID(),
                    ProcessingStatus::ERROR,
                    $e->getMessage(),
                    [
                        'trace' => $e->getTrace(),
                    ],
                )->getMessage()
            );
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
