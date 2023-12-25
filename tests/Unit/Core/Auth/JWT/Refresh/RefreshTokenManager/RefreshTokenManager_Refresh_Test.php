<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\RefreshTokenManager;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class RefreshTokenManager_Refresh_Test extends RefreshTokenManagerBaseTestCase
{
    public const REFRESH_GRACE_PERIOD = 10;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('jwt.refresh.grace-period', self::REFRESH_GRACE_PERIOD);

        $now = now();
        Carbon::setTestNow($now);

        User::factory()->create();
    }

    #[Test]
    public function should_throw_invalid_token_exception_when_now_is_more_than_token_used_at_grace_period(): void
    {
        // Arrange
        $mockedRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email()),
            now(),
            $this->faker->uuid(),
            now()->subSeconds(self::REFRESH_GRACE_PERIOD + 1),
        );

        $mockCacher = $this->mock(Cacher::class, function (MockInterface $mock) use ($mockedRefreshToken) {
            $mock->shouldReceive('find')
                ->once()
                ->with($mockedRefreshToken->id)
                ->andReturn($mockedRefreshToken);
        });
        assert($mockCacher instanceof Cacher);


        // Assert
        $mockException = new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token is already used before.',
            RefreshTokenExceptionCode::TOKEN_IS_USED->value,
        ));
        $this->expectExceptionObject($mockException);


        // Act
        $this->makeService(null, $mockCacher)->refresh($mockedRefreshToken->id);
    }

    #[Test]
    public function should_throw_invalid_token_exception_when_now_is_after_expired_at(): void
    {
        // Arrange
        $mockedRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email()),
            now()->subSeconds(1),
        );

        $mockCacher = $this->mock(Cacher::class, function (MockInterface $mock) use ($mockedRefreshToken) {
            $mock->shouldReceive('find')
                ->once()
                ->with($mockedRefreshToken->id)
                ->andReturn($mockedRefreshToken);
        });
        assert($mockCacher instanceof Cacher);


        // Assert
        $mockException = new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token is expired',
            RefreshTokenExceptionCode::EXPIRED->value
        ));
        $this->expectExceptionObject($mockException);


        // Act
        $this->makeService(null, $mockCacher)->refresh($mockedRefreshToken->id);
    }

    #[Test]
    public function should_throw_invalid_token_exception_when_expired_at_is_null(): void
    {
        // Arrange
        $mockedRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email()),
            null,
        );

        $mockCacher = $this->mock(Cacher::class, function (MockInterface $mock) use ($mockedRefreshToken) {
            $mock->shouldReceive('find')
                ->once()
                ->with($mockedRefreshToken->id)
                ->andReturn($mockedRefreshToken);
        });
        assert($mockCacher instanceof Cacher);


        // Assert
        $mockException = new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token is expired',
            RefreshTokenExceptionCode::EXPIRED->value
        ));
        $this->expectExceptionObject($mockException);


        // Act
        $this->makeService(null, $mockCacher)->refresh($mockedRefreshToken->id);
    }

    #[Test]
    public function should_successfully_return_new_refresh_token(): void
    {
        // Arrange
        $mockUser = User::first();
        assert($mockUser instanceof User);

        $mockedNewRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser((string)$mockUser->id, $this->faker->email()),
            now()->addSeconds(1),
        );

        $mockMapper = $this->mock(
            UserTokenMapperContract::class,
            function (MockInterface $mock) use ($mockedNewRefreshToken, $mockUser) {
                $mock->shouldReceive('map')
                    ->once()
                    ->withArgs(function (User $argUser) use ($mockUser) {
                        try {
                            $this->assertTrue($argUser->is($mockUser));

                            return true;
                        } catch (\Throwable $e) {
                            dump($e);
                            return false;
                        }
                    })->andReturn($mockedNewRefreshToken);
            }
        );
        assert($mockMapper instanceof UserTokenMapperContract);

        $mockedRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser((string)$mockUser->id, $this->faker->email()),
            now()->addSeconds(1),
        );

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedRefreshToken, $mockedNewRefreshToken) {
                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedRefreshToken->id)
                    ->andReturn($mockedRefreshToken);

                $mock->shouldReceive('save')
                    ->once()
                    ->with($mockedNewRefreshToken)
                    ->andReturnNull();

                $oldRefreshTokenAfterMarkAsUsed = clone $mockedRefreshToken;
                $oldRefreshTokenAfterMarkAsUsed->usedAt = now();
                $oldRefreshTokenAfterMarkAsUsed->childID = $mockedNewRefreshToken->id;

                $mock->shouldReceive('save')
                    ->once()
                    ->withArgs(function (
                        RefreshTokenClaims $argRefreshToken,
                    ) use ($oldRefreshTokenAfterMarkAsUsed) {
                        try {
                            $this->assertSame(
                                $oldRefreshTokenAfterMarkAsUsed->id,
                                $argRefreshToken->id,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->user->id,
                                $argRefreshToken->user->id,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->user->userEmail,
                                $argRefreshToken->user->userEmail,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->usedAt,
                                $argRefreshToken->usedAt,
                            );
                            $this->assertEquals(
                                $oldRefreshTokenAfterMarkAsUsed->expiredAt,
                                $argRefreshToken->expiredAt,
                            );
                            $this->assertSame(
                                $oldRefreshTokenAfterMarkAsUsed->childID,
                                $argRefreshToken->childID,
                            );

                            return true;
                        } catch (\Throwable $e) {
                            dump($e);
                            return false;
                        }
                    })->andReturnNull();
            }
        );
        assert($mockCacher instanceof Cacher);


        // Act
        $result = $this->makeService(
            $mockMapper,
            $mockCacher,
        )->refresh($mockedRefreshToken->id);


        // Assert
        $this->assertEquals($mockedNewRefreshToken, $result);
    }

    #[Test]
    public function should_throw_invalid_token_exception_when_child_id_is_not_found(): void
    {
        // Arrange
        $mockUser = User::first();
        assert($mockUser instanceof User);

        $mockedOldRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser((string)$mockUser->id, $this->faker->email()),
            now()->addSeconds(1),
            null,
            now()
        );

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedOldRefreshToken) {
                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedOldRefreshToken->id)
                    ->andReturn($mockedOldRefreshToken);
            }
        );
        assert($mockCacher instanceof Cacher);


        // Assert
        $mockException =  new InvalidTokenException(new ExceptionMessageStandard(
            'Token not found.',
            RefreshTokenExceptionCode::NOT_FOUND->value,
        ));
        $this->expectExceptionObject($mockException);


        // Act
        $this->makeService(
            null,
            $mockCacher,
        )->refresh($mockedOldRefreshToken->id);
    }

    #[Test]
    public function should_successfully_return_latest_child_token_that_been_created_when_old_refresh_token_is_refresh_again_in_the_grace_period_timeframe(): void
    {
        // Arrange
        $mockUser = User::first();
        assert($mockUser instanceof User);

        $mockedOldRefreshToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser((string)$mockUser->id, $this->faker->email()),
            now()->addSeconds(1),
            $this->faker->uuid(),
            now(),
        );

        $mockedChildRefreshToken = new RefreshTokenClaims(
            $mockedOldRefreshToken->childID,
            new RefreshTokenClaimsUser((string)$mockUser->id, $this->faker->email()),
            now()->addSeconds(1),
        );

        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($mockedOldRefreshToken, $mockedChildRefreshToken) {
                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedOldRefreshToken->id)
                    ->andReturn($mockedOldRefreshToken);

                $mock->shouldReceive('find')
                    ->once()
                    ->with($mockedChildRefreshToken->id)
                    ->andReturn($mockedChildRefreshToken);
            }
        );
        assert($mockCacher instanceof Cacher);


        // Act
        $result = $this->makeService(
            null,
            $mockCacher,
        )->refresh($mockedOldRefreshToken->id);


        // Assert
        $this->assertEquals($mockedChildRefreshToken, $result);
    }
}
