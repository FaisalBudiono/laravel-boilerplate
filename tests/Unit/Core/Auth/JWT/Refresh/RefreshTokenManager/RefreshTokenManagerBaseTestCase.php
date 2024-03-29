<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\RefreshTokenManager;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenManager;
use Tests\TestCase;

abstract class RefreshTokenManagerBaseTestCase extends TestCase
{
    protected function makeService(
        ?UserTokenMapperContract $userTokenMapper = null,
        ?Cacher $cacher = null,
    ): RefreshTokenManager {
        if (is_null($userTokenMapper)) {
            $userTokenMapper = $this->mock(UserTokenMapperContract::class);
        }

        if (is_null($cacher)) {
            $cacher = $this->mock(Cacher::class);
        }

        return new RefreshTokenManager($userTokenMapper, $cacher);
    }
}
