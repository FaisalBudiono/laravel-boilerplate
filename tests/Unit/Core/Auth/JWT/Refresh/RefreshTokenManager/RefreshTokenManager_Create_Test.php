<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\RefreshTokenManager;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Models\User\User;
use Carbon\Carbon;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class RefreshTokenManager_Create_Test extends RefreshTokenManagerBaseTestCase
{
    #[Test]
    public function should_return_refresh_token_claims_after_token_is_saved(): void
    {
        // Arrange
        $mockUser = User::factory()->create()->fresh();

        $mockedRefreshTokenClaims = new RefreshTokenClaims(
            $this->faker->numerify,
            new RefreshTokenClaimsUser($this->faker->numerify, $this->faker->email),
            Carbon::parse($this->faker->dateTime),
            null,
        );


        // Assert
        $mockUserTokenMapper = $this->mock(
            UserTokenMapperContract::class,
            function (MockInterface $mock) use ($mockUser, $mockedRefreshTokenClaims) {
                $mock->shouldReceive('map')
                    ->once()
                    ->withArgs(function (User $argUser) use ($mockUser) {
                        $this->assertEquals($argUser, $mockUser);

                        return true;
                    })->andReturn($mockedRefreshTokenClaims);
            }
        );
        assert($mockUserTokenMapper instanceof UserTokenMapperContract);

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedRefreshTokenClaims) {
                $mock->shouldReceive('save')
                    ->once()
                    ->with($mockedRefreshTokenClaims)
                    ->andReturnNull();
            }
        );
        assert($mockCacher instanceof Cacher);


        // Act
        $result = $this->makeService(
            $mockUserTokenMapper,
            $mockCacher,
        )->create($mockUser);


        // Assert
        $this->assertEquals($mockedRefreshTokenClaims, $result);
    }
}
