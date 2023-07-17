<?php

namespace App\Http\Controllers\Healthcheck;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
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
        protected LoggerMessageFactoryContract $logFormatter,
    ) {
    }

    public function index(HealthcheckRequest $request)
    {
        try {
            Log::info(
                $this->logFormatter->makeHTTPStart(
                    'Healthcheck endpoint',
                    [],
                ),
            );

            $status = $this->core->getHealthiness($request);

            Log::info($this->logFormatter->makeHTTPSuccess('Healthcheck endpoint', []));

            return response()
                ->json($status->toArray())
                ->setStatusCode(
                    $status->isHealthy()
                        ? Response::HTTP_OK
                        : Response::HTTP_INTERNAL_SERVER_ERROR
                );
        } catch (Exception $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric);
        }
    }
}
