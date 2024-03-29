<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Http\Middleware\AuthenticatedByJWT;
use App\Http\Middleware\LoggingMiddleware;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderMiddleware implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            AuthenticatedByJWT::class,
            fn (Application $app) => new AuthenticatedByJWT(
                $app->make(JWTSigner::class),
                $app->make(JWTParser::class),
            )
        );

        $app->bind(
            LoggingMiddleware::class,
            fn (Application $app) => new LoggingMiddleware(
                $app->make(LogMessageDirectorContract::class),
                $app->make(LogMessageBuilderContract::class),
            )
        );

        $app->bind(
            XRequestIDMiddleware::class,
            fn (Application $app) => new XRequestIDMiddleware(
                $app->make(Randomizer::class),
            )
        );
    }
}
