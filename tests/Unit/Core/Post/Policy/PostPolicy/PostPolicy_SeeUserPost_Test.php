<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\Policy\PostPolicy;

use App\Models\Permission\Enum\RoleName;
use App\Models\User\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PostPolicy_SeeUserPost_Test extends PostPolicyBaseTestCase
{
    #[Test]
    public function should_return_true_when_user_is_admin(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(RoleName::ADMIN);

        $userFilter = User::factory()->create();


        // Assert
        $this->assertTrue($this->makeService()->seeUserPost($user, $userFilter));
    }

    #[Test]
    #[DataProvider('notAllowRolesDataProvider')]
    public function should_return_true_when_user_is_user_filter(
        array $roles,
    ): void {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(...$roles);



        // Assert
        $this->assertTrue($this->makeService()->seeUserPost($user, $user));
    }

    #[Test]
    #[DataProvider('notAllowRolesDataProvider')]
    public function should_return_false_when_not_admin(
        array $roles,
    ): void {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(...$roles);

        $userFilter = User::factory()->create();


        // Assert
        $this->assertFalse($this->makeService()->seeUserPost($user, $userFilter));
    }
}
