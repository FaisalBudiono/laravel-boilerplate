<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Services\JWT\JWTGuard;
use App\Services\JWT\JWTGuardContract;
use App\Services\JWT\Parser\JWTParser;
use App\Services\JWT\Parser\JWTParserLcobucci;
use App\Services\JWT\Parser\JWTRefreshParser;
use App\Services\JWT\Parser\JWTRefreshParserLcobucci;
use App\Services\JWT\Signer\JWTRefreshSigner;
use App\Services\JWT\Signer\JWTRefreshSignerLcobucci;
use App\Services\JWT\Signer\JWTSigner;
use App\Services\JWT\Signer\JWTSignerLcobucci;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class CoreBinderJWT implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            JWTGuardContract::class,
            fn (Application $app) => new JWTGuard(
                $app->make(Request::class),
                $app->make(JWTSigner::class),
                $app->make(JWTParser::class),
            ),
        );

        $app->bind(
            JWTParser::class,
            fn (Application $app) => new JWTParserLcobucci(),
        );

        $app->bind(
            JWTRefreshParser::class,
            fn (Application $app) => new JWTRefreshParserLcobucci(),
        );

        $app->bind(
            JWTRefreshSigner::class,
            fn (Application $app) => new JWTRefreshSignerLcobucci(),
        );

        $app->bind(
            JWTSigner::class,
            fn (Application $app) => new JWTSignerLcobucci(),
        );
    }
}
