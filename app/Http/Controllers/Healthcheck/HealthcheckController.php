<?php

declare(strict_types=1);

namespace App\Http\Controllers\Healthcheck;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Healthcheck\HealthcheckRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

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

            if ($status->isHealthy()) {
                Log::info($this->logFormatter->makeHTTPSuccess('Healthcheck endpoint', []));

                return response()
                    ->json($status->toArray())
                    ->setStatusCode(Response::HTTP_OK);
            }

            Log::emergency($this->logFormatter->makeHTTPSuccess('Healthcheck endpoint', [
                'detail' => $status->toArrayDetail(),
            ]));

            return response()
                ->json($status->toArray())
                ->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (Throwable $e) {
            Log::error($this->logFormatter->makeHTTPError($e));
            throw new InternalServerErrorException(new ExceptionMessageGeneric(), $e);
        }
    }
}
