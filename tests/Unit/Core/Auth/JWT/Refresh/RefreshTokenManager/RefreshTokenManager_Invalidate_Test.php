<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\RefreshTokenManager;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class RefreshTokenManager_Invalidate_Test extends RefreshTokenManagerBaseTestCase
{
    #[Test]
    public function should_invalidate_refresh_token()
    {
        // Arrange
        $token = $this->faker->sentence();


        // Assert
        $mockCacher = $this->mock(
            Cacher::class,
            function (MockInterface $mock) use ($token) {
                $mock->shouldReceive('invalidate')
                    ->once()
                    ->with($token);
            }
        );
        assert($mockCacher instanceof Cacher);


        // Act
        $this->makeService(null, $mockCacher)->invalidate($token);
    }
}
