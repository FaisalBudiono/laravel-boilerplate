<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\Policy\UserPolicy;

use App\Models\Permission\Enum\RoleName;
use App\Models\User\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

abstract class UserPolicySameTestCase extends UserPolicyBaseTestCase
{
    abstract protected function methodName(): string;

    #[Test]
    public function should_return_true_when_user_is_admin(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(RoleName::ADMIN);

        $targetUser = User::factory()->create();


        // Assert
        $this->assertTrue($this->makeService()->{$this->methodName()}($user, $targetUser));
    }

    #[Test]
    #[DataProvider('notAllowRolesDataProvider')]
    public function should_return_true_when_user_is_same(
        array $roles,
    ): void {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(...$roles);

        $targetUser = $user;


        // Assert
        $this->assertTrue($this->makeService()->{$this->methodName()}($user, $targetUser));
    }

    #[Test]
    #[DataProvider('notAllowRolesDataProvider')]
    public function should_return_false_when_not_admin_nor_same(
        array $roles,
    ): void {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(...$roles);

        $targetUser = User::factory()->create();


        // Assert
        $this->assertFalse($this->makeService()->{$this->methodName()}($user, $targetUser));
    }
}
