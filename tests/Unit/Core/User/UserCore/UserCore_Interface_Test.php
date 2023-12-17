<?php

namespace Tests\Unit\Core\User\UserCore;

use App\Core\User\UserCoreContract;
use PHPUnit\Framework\Attributes\Test;

class UserCore_Interface_Test extends UserCoreBaseTestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(UserCoreContract::class, $this->makeService());
    }
}
