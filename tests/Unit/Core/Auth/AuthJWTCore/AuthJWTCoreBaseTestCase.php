<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\AuthJWTCore;

use App\Core\Auth\AuthJWTCore;
use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use App\Core\Auth\JWT\Signer\JWTSigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AuthJWTCoreBaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeService(
        ?JWTMapperContract $jwtMapper = null,
        ?JWTSigner $jwtSigner = null,
        ?RefreshTokenManagerContract $refreshTokenManager = null,
    ): AuthJWTCore {
        if (is_null($jwtMapper)) {
            $jwtMapper = $this->mock(JWTMapperContract::class);
        }

        if (is_null($jwtSigner)) {
            $jwtSigner = $this->mock(JWTSigner::class);
        }

        if (is_null($refreshTokenManager)) {
            $refreshTokenManager = $this->mock(
                RefreshTokenManagerContract::class,
            );
        }

        return new AuthJWTCore(
            $jwtMapper,
            $jwtSigner,
            $refreshTokenManager,
        );
    }
}
