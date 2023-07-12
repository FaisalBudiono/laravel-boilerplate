<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\RefreshTokenManager;

use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use PHPUnit\Framework\Attributes\Test;

class RefreshTokenManager_Interface_Test extends RefreshTokenManagerBaseTestCase
{
    #[Test]
    public function should_implement_right_interface()
    {
        // Assert
        $this->assertInstanceOf(RefreshTokenManagerContract::class, $this->makeService());
    }
}
