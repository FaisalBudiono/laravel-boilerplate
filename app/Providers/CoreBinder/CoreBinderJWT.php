<?php

namespace App\Providers\CoreBinder;

use App\Core\Auth\JWT\JWTGuard;
use App\Core\Auth\JWT\JWTGuardContract;
use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Parser\JWTParserLcobucci;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\Signer\JWTSignerLcobucci;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class CoreBinderJWT implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(JWTGuardContract::class, function (Application $app) {
            return new JWTGuard(
                $app->make(Request::class),
                $app->make(JWTParser::class),
                $app->make(JWTSigner::class),
            );
        });

        $app->bind(JWTParser::class, function (Application $app) {
            return new JWTParserLcobucci;
        });

        $app->bind(JWTSigner::class, function (Application $app) {
            return new JWTSignerLcobucci;
        });
    }
}
