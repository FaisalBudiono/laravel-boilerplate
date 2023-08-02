<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Http\Middleware\AuthenticatedByJWT;
use App\Http\Middleware\LoggingMiddleware;
use App\Http\Middleware\XRequestIDMiddleware;
use Tests\Unit\Providers\CoreBinder\Dependencies\DependencyFactoryMockery;

class CoreBinderTestCaseMiddleware extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            AuthenticatedByJWT::class => [
                AuthenticatedByJWT::class,
                [
                    new DependencyFactoryMockery($this->test, JWTSigner::class),
                    new DependencyFactoryMockery($this->test, JWTParser::class),
                ],
            ],
            LoggingMiddleware::class => [
                LoggingMiddleware::class,
                [
                    new DependencyFactoryMockery($this->test, LoggerMessageFactoryContract::class),
                ],
            ],
            XRequestIDMiddleware::class => [
                XRequestIDMiddleware::class,
                [
                    new DependencyFactoryMockery($this->test, Randomizer::class),
                ],
            ],
        ];
    }
}
