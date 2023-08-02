<?php

namespace App\Http\Middleware;

use App\Core\Logger\Message\LoggerMessageFactoryContract;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    public function __construct(
        protected LoggerMessageFactoryContract $logFormatter,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $endpointInfo = "{$request->method()} {$request->url()}";

        Log::info(
            $this->logFormatter->makeHTTPStart(
                $endpointInfo,
                $this->cleanUpInput($request->all()),
            )
        );

        /** @var JsonResponse */
        $response = $next($request);

        $exception = $response->exception;

        if (is_null($exception)) {
            Log::info(
                $this->logFormatter->makeHTTPSuccess($endpointInfo, [])
            );

            return $response;
        }

        $exceptionMessage = $this->logFormatter->makeHTTPError(
            $exception->getPrevious() ?? $exception
        );
        if ($exception->getCode() < 500) {
            Log::warning($exceptionMessage);

            return $response;
        }

        Log::error($exceptionMessage);

        return $response;
    }

    protected function cleanUpInput(array $input): array
    {
        return collect($input)
            ->reject(fn ($value, $key) => collect(['password'])->contains($key))
            ->toArray();
    }
}
