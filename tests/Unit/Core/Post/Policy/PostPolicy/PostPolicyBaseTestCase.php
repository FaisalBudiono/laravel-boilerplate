<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\Policy\PostPolicy;

use App\Core\Post\Policy\PostPolicy;
use App\Models\Permission\Enum\RoleName;
use Database\Seeders\Base\RoleSeeder;
use Tests\TestCase;

abstract class PostPolicyBaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public static function notAllowRolesDataProvider(): array
    {
        return [
            'not admin' => [
                [
                    RoleName::NORMAL,
                ],
            ],
            'dont have role' => [
                [],
            ],
        ];
    }

    protected function makeService(): PostPolicy
    {
        return new PostPolicy();
    }
}
