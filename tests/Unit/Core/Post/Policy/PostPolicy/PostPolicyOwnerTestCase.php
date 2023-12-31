<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\Policy\PostPolicy;

use App\Models\Permission\Enum\RoleName;
use App\Models\Post\Post;
use App\Models\User\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

abstract class PostPolicyOwnerTestCase extends PostPolicyBaseTestCase
{
    abstract protected function methodName(): string;

    #[Test]
    public function should_return_true_when_user_is_admin(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(RoleName::ADMIN);

        $post = Post::factory()->create();


        // Assert
        $this->assertTrue($this->makeService()->{$this->methodName()}($user, $post));
    }

    #[Test]
    #[DataProvider('notAllowRolesDataProvider')]
    public function should_return_true_when_user_is_owner(
        array $roles,
    ): void {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(...$roles);

        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);


        // Assert
        $this->assertTrue($this->makeService()->{$this->methodName()}($user, $post));
    }

    #[Test]
    #[DataProvider('notAllowRolesDataProvider')]
    public function should_return_false_when_not_admin_nor_not_owner(
        array $roles,
    ): void {
        // Arrange
        $user = User::factory()->create();
        $user->syncRoles(...$roles);

        $post = Post::factory()->create();


        // Assert
        $this->assertFalse($this->makeService()->{$this->methodName()}($user, $post));
    }
}
