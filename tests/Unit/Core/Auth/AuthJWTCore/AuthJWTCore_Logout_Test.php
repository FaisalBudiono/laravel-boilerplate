<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\AuthJWTCore;

use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use App\Port\Core\Auth\LogoutPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class AuthJWTCore_Logout_Test extends AuthJWTCoreBaseTestCase
{
    protected LogoutPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(LogoutPort::class);
    }

    #[Test]
    public function should_invalidate_token(): void
    {
        // Arrange
        $token = $this->faker->sentence;


        // Assert
        $this->mockRequest->shouldReceive('getRefreshToken')->once()->andReturn($token);

        $mockRefreshTokenManager = $this->mock(
            RefreshTokenManagerContract::class,
            function (MockInterface $mock) use ($token) {
                $mock->shouldReceive('invalidate')
                    ->once()
                    ->with($token);
            }
        );
        assert($mockRefreshTokenManager instanceof RefreshTokenManagerContract);


        // Act
        $this->makeService(
            null,
            null,
            $mockRefreshTokenManager,
        )->logout($this->mockRequest);
    }
}
