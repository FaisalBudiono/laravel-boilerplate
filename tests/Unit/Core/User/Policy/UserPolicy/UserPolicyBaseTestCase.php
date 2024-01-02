<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\Policy\UserPolicy;

use App\Core\User\Policy\UserPolicy;
use App\Models\Permission\Enum\RoleName;
use Database\Seeders\Base\RoleSeeder;
use Tests\TestCase;

abstract class UserPolicyBaseTestCase extends TestCase
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

    protected function makeService(): UserPolicy
    {
        return new UserPolicy();
    }
}
