<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\RefreshTokenManager;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use App\Models\User\User;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class RefreshTokenManager_Refresh_Test extends RefreshTokenManagerBaseTestCase
{
    #[Test]
    public function should_throw_error_exception_when_cacher_is_said_token_is_already_used_and_successfully_invalidate_all_children_token()
    {
        // Arrange
        $mockedTokenID = $this->faker->uuid();

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use (
                $mockedTokenID,
            ) {
                $mock->shouldReceive('isUnused')
                    ->once()
                    ->with($mockedTokenID)
                    ->andReturn(false);

                $mock->shouldReceive('deleteAllGenerations')
                    ->once()
                    ->with($mockedTokenID)
                    ->andReturnNull();
            }
        );
        assert($mockCacher instanceof Cacher);

        $service = $this->makeService(null, $mockCacher);


        // Assert
        $expectedException =  new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token is already used before.',
            RefreshTokenExceptionCode::TOKEN_IS_USED->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $service->refresh($mockedTokenID);
    }

    #[Test]
    public function should_throw_error_exception_when_cacher_is_throwing_error_when_refresh_token_already_expired()
    {
        // Arrange
        $user = User::factory()->create()->fresh();
        $mockedTokenID = $this->faker->uuid();

        $mockedToken = new RefreshTokenClaims(
            $this->faker->uuid,
            new RefreshTokenClaimsUser(
                $user->id,
                $this->faker->email,
            ),
            now()->subYear(),
        );
        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedToken, $mockedTokenID) {
                $mock->shouldReceive('isUnused')
                    ->once()
                    ->with($mockedTokenID)
                    ->andReturn(true);

                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedTokenID)
                    ->andReturn($mockedToken);
            }
        );
        assert($mockCacher instanceof Cacher);

        $service = $this->makeService(null, $mockCacher);


        // Assert
        $expectedException = new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token is expired',
            RefreshTokenExceptionCode::EXPIRED->value
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $service->refresh($mockedTokenID);
    }

    #[Test]
    public function should_return_refreshed_token_with_old_token_as_child_id_when_successfully_create_refresh_token()
    {
        // Arrange
        $user = User::factory()->create()->fresh();
        $mockedTokenID = $this->faker->uuid();

        $mockedRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid,
            new RefreshTokenClaimsUser(
                $user->id,
                $this->faker->email,
            ),
            now()->addYear(),
        );
        $mockMapper = $this->mock(
            UserTokenMapperContract::class,
            function (MockInterface $mock) use ($mockedRefreshToken) {
                $mock->shouldReceive('map')
                    ->once()
                    ->andReturn($mockedRefreshToken);
            },
        );
        assert($mockMapper instanceof UserTokenMapperContract);

        $mockedToken = new RefreshTokenClaims(
            $this->faker->uuid,
            new RefreshTokenClaimsUser(
                $user->id,
                $this->faker->email,
            ),
            now()->addYear(),
        );

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use (
                $mockedTokenID,
                $mockedToken,
                $mockedRefreshToken,
            ) {
                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedTokenID)
                    ->andReturn($mockedToken);

                $mock->shouldReceive('isUnused')
                    ->once()
                    ->with($mockedTokenID)
                    ->andReturn(true);

                $mock->shouldReceive('save')
                    ->once()
                    ->with($mockedRefreshToken)
                    ->andReturnNull();

                $mock->shouldReceive('invalidate')
                    ->once()
                    ->with($mockedTokenID)
                    ->andReturnNull();

                $mock->shouldReceive('setChildID')
                    ->once()
                    ->with($mockedTokenID, $mockedRefreshToken->id)
                    ->andReturnNull();
            }
        );
        assert($mockCacher instanceof Cacher);

        $service = $this->makeService($mockMapper, $mockCacher);


        // Act
        $result = $service->refresh($mockedTokenID);


        // Assert
        $this->assertEquals($mockedRefreshToken, $result);
    }
}
