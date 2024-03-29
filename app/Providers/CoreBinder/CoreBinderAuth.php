<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\Auth\AuthJWTCore;
use App\Core\Auth\AuthJWTCoreContract;
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

class CoreBinderAuth implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            AuthJWTCoreContract::class,
            fn (Application $app) => new AuthJWTCore(
                $app->make(JWTMapperContract::class),
                $app->make(JWTSigner::class),
                $app->make(RefreshTokenManagerContract::class),
            )
        );

        $app->bind(
            JWTGuardContract::class,
            fn (Application $app) => new JWTGuard(
                $app->make(Request::class),
                $app->make(JWTParser::class),
                $app->make(JWTSigner::class),
            )
        );

        $app->bind(
            JWTMapperContract::class,
            fn (Application $app) => new JWTMapper()
        );

        $app->bind(
            JWTParser::class,
            fn (Application $app) => new JWTParserLcobucci()
        );

        $app->bind(
            JWTSigner::class,
            fn (Application $app) => new JWTSignerLcobucci()
        );

        $app->bind(
            RefreshTokenCacher::class,
            fn (Application $app) => new RefreshTokenCacherLaravel()
        );

        $app->bind(
            RefreshTokenManagerContract::class,
            fn (Application $app) => new RefreshTokenManager(
                $app->make(RefreshTokenUserTokenMapperContract::class),
                $app->make(RefreshTokenCacher::class),
            )
        );

        $app->bind(
            RefreshTokenUserTokenMapperContract::class,
            fn (Application $app) => new RefreshTokenUserTokenMapper()
        );
    }
}
