<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Logger\Logger;
use App\Services\Logger\Message\Enum\ProcessingStatus;
use App\Services\Logger\Message\LogMessageDirectorContract;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LoggingMiddleware
{
    public function __construct(
        protected LogMessageDirectorContract $logDirector,
        protected Logger $logger,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $this->logger->info(
            $this->logDirector->buildHTTP(
                $this->logDirector->makeBuilder(),
                ProcessingStatus::BEGIN,
            )->message('start')
                ->meta([
                    'input' => $this->cleanUpInput($request->all()),
                    'headers' => [
                        'user-agent' => $request->userAgent(),
                    ],
                ])->build(),
        );

        $response = $next($request);
        assert($response instanceof SymfonyResponse);

        $exception = $this->getResponseException($response);

        if (is_null($exception)) {
            $this->logger->info(
                $this->logDirector->buildHTTP(
                    $this->logDirector->makeBuilder(),
                    ProcessingStatus::SUCCESS,
                )->message('end')
                    ->build(),
            );

            return $response;
        }

        $prevException = $exception->getPrevious() ?? $exception;
        $exceptionMessage = $this->logDirector->buildForException(
            $this->logDirector->buildHTTP(
                $this->logDirector->makeBuilder(),
                ProcessingStatus::ERROR,
            ),
            $prevException,
        )->build();

        if ($this->isHTTPClientError($exception->getCode())) {
            $this->logger->warning($exceptionMessage);

            return $response;
        }

        $this->logger->error($exceptionMessage);

        return $response;
    }

    protected function cleanUpInput(array $input): array
    {
        return collect($input)
            ->reject(fn ($value, $key) => collect(['password'])->contains($key))
            ->toArray();
    }

    protected function getResponseException(SymfonyResponse $res): ?\Throwable
    {
        if (!($res instanceof JsonResponse || $res instanceof Response)) {
            return null;
        }

        return $res->exception ?? null;
    }

    protected function isHTTPClientError(int $httpCode): bool
    {
        return $httpCode > 399 && $httpCode < 500;
    }
}
