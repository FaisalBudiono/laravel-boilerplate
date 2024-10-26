<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceContract;
use App\Services\Auth\RefreshToken\RefreshTokenManager;
use App\Services\Auth\RefreshToken\RefreshTokenManagerPermanent;
use App\Services\Formatter\Randomizer\Randomizer;
use App\Services\JWT\Parser\JWTRefreshParser;
use App\Services\JWT\Signer\JWTRefreshSigner;
use App\Services\JWT\Signer\JWTSigner;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderAuth implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            AuthServiceContract::class,
            fn (Application $app) => new AuthService(
                signer: $app->make(JWTSigner::class),
                refreshTokenManager: $app->make(RefreshTokenManager::class),
            ),
        );

        $app->bind(
            RefreshTokenManager::class,
            fn (Application $app) => new RefreshTokenManagerPermanent(
                signer: $app->make(JWTRefreshSigner::class),
                parser: $app->make(JWTRefreshParser::class),
                randomizer: $app->make(Randomizer::class),
            ),
        );
    }
}
