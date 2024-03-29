<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\AuthJWTCore;

use App\Core\Auth\AuthJWTCoreContract;
use PHPUnit\Framework\Attributes\Test;

class AuthJWTCore_Interface_Test extends AuthJWTCoreBaseTestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(AuthJWTCoreContract::class, $this->makeService());
    }
}
