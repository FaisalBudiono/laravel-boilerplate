<?php

namespace Tests\Unit\Core\Auth\AuthJWTCore;

use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Models\User\User;
use App\Port\Core\Auth\GetRefreshTokenPort;
use Carbon\Carbon;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class AuthJWTCore_Refresh_Test extends AuthJWTCoreBaseTestCase
{
    protected GetRefreshTokenPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(GetRefreshTokenPort::class);
    }

    #[Test]
    public function should_return_token_pair_when_refresh_token_can_be_refreshed(): void
    {
        // Arrange
        $user = User::factory()->create()->fresh();
        $token = $this->faker->sentence;


        // Assert
        $this->mockRequest->shouldReceive('getRefreshToken')->once()->andReturn($token);

        $mockedClaims = $this->makeFakeClaims();
        $mockJWTMapper = $this->mock(
            JWTMapperContract::class,
            function (MockInterface $mock) use ($mockedClaims, $user) {
                $mock->shouldReceive('map')
                    ->once()
                    ->withArgs(function (User $argUser) use ($user) {
                        $this->assertEquals($user, $argUser);
                        return true;
                    })->andReturn($mockedClaims);
            }
        );
        assert($mockJWTMapper instanceof JWTMapperContract);

        $mockedAccessToken = $this->faker->sentence;
        $mockJWTSigner = $this->mock(
            JWTSigner::class,
            function (MockInterface $mock) use ($mockedAccessToken, $mockedClaims) {
                $mock->shouldReceive('sign')
                    ->once()
                    ->with($mockedClaims)
                    ->andReturn($mockedAccessToken);
            }
        );
        assert($mockJWTSigner instanceof JWTSigner);

        $mockedRefreshTokenClaims = $this->makeFakeRefreshTokenClaims();
        $mockRefreshTokenManager = $this->mock(
            RefreshTokenManagerContract::class,
            function (MockInterface $mock) use ($token, $mockedRefreshTokenClaims) {
                $mock->shouldReceive('refresh')
                    ->once()
                    ->with($token)
                    ->andReturn($mockedRefreshTokenClaims);
            }
        );
        assert($mockRefreshTokenManager instanceof RefreshTokenManagerContract);


        // Act
        $result = $this->makeService(
            $mockJWTMapper,
            $mockJWTSigner,
            $mockRefreshTokenManager,
        )->refresh($this->mockRequest);


        // Assert
        $expectedResult = new TokenPair(
            $mockedAccessToken,
            $mockedRefreshTokenClaims->id,
        );
        $this->assertEquals($expectedResult, $result);
    }

    protected function makeFakeClaims(): Claims
    {
        return new Claims(
            new ClaimsUser($this->faker->uuid(), $this->faker->email),
            collect([
                $this->faker->sentence()
            ]),
            Carbon::parse($this->faker->dateTime),
            Carbon::parse($this->faker->dateTime),
            Carbon::parse($this->faker->dateTime),
        );
    }

    protected function makeFakeRefreshTokenClaims(): RefreshTokenClaims
    {
        return new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser(1, $this->faker->email),
            Carbon::parse($this->faker->dateTime),
            $this->faker->uuid(),
        );
    }
}
