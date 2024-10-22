<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Services\Auth\AuthService;
use App\Services\Auth\AuthServiceContract;
use App\Services\JWT\Signer\JWTSigner;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderAuth implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            AuthServiceContract::class,
            fn (Application $app) => new AuthService(
                $app->make(JWTSigner::class),
            ),
        );
    }
}
