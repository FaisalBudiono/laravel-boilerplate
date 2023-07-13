<?php

namespace App\Providers\CoreBinder;

use App\Core\Auth\JWT\JWTGuard;
use App\Core\Auth\JWT\JWTGuardContract;
use App\Core\Auth\JWT\Mapper\JWTMapper;
use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Parser\JWTParserLcobucci;
use App\Core\Auth\JWT\Refresh\Cacher\Cacher as RefreshTokenCacher;
use App\Core\Auth\JWT\Refresh\Cacher\CacherLaravel as RefreshTokenCacherLaravel;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapper as RefreshTokenUserTokenMapper;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract as RefreshTokenUserTokenMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenManager;
use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
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

        $app->bind(JWTMapperContract::class, function (Application $app) {
            return new JWTMapper;
        });

        $app->bind(JWTParser::class, function (Application $app) {
            return new JWTParserLcobucci;
        });

        $app->bind(JWTSigner::class, function (Application $app) {
            return new JWTSignerLcobucci;
        });

        $app->bind(RefreshTokenCacher::class, function (Application $app) {
            return new RefreshTokenCacherLaravel;
        });

        $app->bind(RefreshTokenManagerContract::class, function (Application $app) {
            return new RefreshTokenManager(
                $app->make(RefreshTokenUserTokenMapperContract::class),
                $app->make(RefreshTokenCacher::class),
            );
        });

        $app->bind(RefreshTokenUserTokenMapperContract::class, function (Application $app) {
            return new RefreshTokenUserTokenMapper;
        });
    }
}
