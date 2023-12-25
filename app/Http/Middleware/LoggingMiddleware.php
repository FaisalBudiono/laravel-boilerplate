<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    public function __construct(
        protected LogMessageDirectorContract $logDirector,
        protected LogMessageBuilderContract $logBuilder,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info(
            $this->logDirector->buildBegin(
                clone $this->logBuilder
            )->message('start')
                ->meta($this->cleanUpInput($request->all()))
                ->build()
        );

        /** @var JsonResponse */
        $response = $next($request);

        $exception = $response->exception;

        if (is_null($exception)) {
            Log::info(
                $this->logDirector->buildSuccess(
                    clone $this->logBuilder
                )->message('end')
                    ->build()
            );

            return $response;
        }

        $prevException = $exception->getPrevious() ?? $exception;
        $exceptionMessage = $this->logDirector->buildForException(
            $this->logDirector->buildError(
                clone $this->logBuilder
            ),
            $prevException,
        )->build();

        if ($this->isHTTPClientError($exception->getCode())) {
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

    protected function isHTTPClientError(int $httpCode): bool
    {
        return $httpCode > 399 && $httpCode < 500;
    }
}
