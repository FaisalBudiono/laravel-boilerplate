<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\JWTGuard;

use App\Core\Auth\JWT\JWTGuardContract;
use Illuminate\Contracts\Auth\Guard;
use PHPUnit\Framework\Attributes\Test;

class JWTGuard_Interface_Test extends JWTGuardBaseTestCase
{
    #[Test]
    public function should_implement_guard_interface(): void
    {
        // Arrange
        $service = $this->makeService();


        // Assert
        $this->assertInstanceOf(JWTGuardContract::class, $service);
        $this->assertInstanceOf(Guard::class, $service);
    }
}
