<?php

namespace Tests\Unit\Core\Auth\AuthJWTCore;

use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use App\Port\Core\Auth\LogoutPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class AuthJWTCore_Logout_Test extends AuthJWTCoreBaseTestCase
{
    protected LogoutPort $mockRequest;

    /** @var (\Mockery\ExpectationInterface|\Mockery\Expectation|\Mockery\HigherOrderMessage)[] */
    protected $mockedRequestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(LogoutPort::class, function (MockInterface $mock) {
            $this->getClassMethods(LogoutPort::class)->each(
                fn (string $methodName) =>
                $this->mockedRequestMethods[$methodName] = $mock->shouldReceive($methodName)
            );
        });
    }

    #[Test]
    public function should_invalidate_token()
    {
        // Arrange
        $token = $this->faker->sentence;


        // Assert
        $this->mockedRequestMethods['getRefreshToken']->once()->andReturn($token);

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
