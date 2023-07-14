<?php

namespace Tests\Unit\Core\Auth\AuthJWTCore;

use App\Core\Auth\AuthJWTCoreContract;
use PHPUnit\Framework\Attributes\Test;

class AuthJWTCore_Interface_Test extends AuthJWTCoreBaseTestCase
{
    #[Test]
    public function should_implement_right_interface()
    {
        // Assert
        $this->assertInstanceOf(AuthJWTCoreContract::class, $this->makeService());
    }
}
