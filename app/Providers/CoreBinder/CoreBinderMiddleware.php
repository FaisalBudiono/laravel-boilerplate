<?php

namespace App\Providers\CoreBinder;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Http\Middleware\AuthenticatedByJWT;
use App\Http\Middleware\LoggingMiddleware;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderMiddleware implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(AuthenticatedByJWT::class, function (Application $app) {
            return new AuthenticatedByJWT(
                $app->make(JWTSigner::class),
                $app->make(JWTParser::class),
            );
        });

        $app->bind(LoggingMiddleware::class, function (Application $app) {
            return new LoggingMiddleware(
                $app->make(LoggerMessageFactoryContract::class),
            );
        });

        $app->bind(XRequestIDMiddleware::class, function (Application $app) {
            return new XRequestIDMiddleware(
                $app->make(Randomizer::class),
            );
        });
    }
}
