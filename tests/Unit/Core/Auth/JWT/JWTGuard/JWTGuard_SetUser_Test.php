<?php

namespace Tests\Unit\Core\Auth\JWT\JWTGuard;

use App\Models\User\User;
use PHPUnit\Framework\Attributes\Test;

class JWTGuard_SetUser_Test extends JWTGuardBaseTestCase
{
    #[Test]
    public function should_be_able_to_set_user_in_guard()
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();
        $service = $this->makeService();


        // Act
        $service->setUser($mockedUser);

        $resultUser = $service->user();


        // Assert
        $this->assertEquals($mockedUser, $resultUser);
    }
}
