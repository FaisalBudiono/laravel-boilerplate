<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\Policy\UserPolicy;

use App\Models\Permission\Enum\RoleName;
use App\Models\User\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class UserPolicy_SeeAll_Test extends UserPolicyBaseTestCase
{
    #[Test]
    public function should_return_true_when_user_is_admin(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(RoleName::ADMIN);


        // Assert
        $this->assertTrue($this->makeService()->seeAll($user));
    }

    #[Test]
    #[DataProvider('notAllowRolesDataProvider')]
    public function should_return_false_when_user_is(
        array $roles,
    ): void {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(...$roles);


        // Assert
        $this->assertFalse($this->makeService()->seeAll($user));
    }
}
