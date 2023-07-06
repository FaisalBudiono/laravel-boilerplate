<?php

namespace Tests\Unit\Core\Auth\JWT\JWTGuard;

use Illuminate\Contracts\Auth\Guard;
use PHPUnit\Framework\Attributes\Test;

class JWTGuard_Interface_Test extends JWTGuardBaseTestCase
{
    #[Test]
    public function should_implement_guard_interface()
    {
        // Arrange
        $service = $this->makeService();


        // Assert
        $this->assertInstanceOf(Guard::class, $service);
    }
}
