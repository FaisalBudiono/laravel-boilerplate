<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Seeder\Base;

use App\Models\Permission\Enum\RoleName;
use Database\Seeders\Base\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public const DEFAULT_GUARD_NAME = 'auth.defaults.guard';

    protected string $mockedGuardName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedGuardName = $this->faker->word();

        Config::set(self::DEFAULT_GUARD_NAME, $this->mockedGuardName);
    }

    #[Test]
    public function should_seed_default_roles_once_if_the_default_guard_not_changed_when_called_multiple_times(): void
    {
        // Arrange
        $totalSeedCalled = $this->faker->numberBetween(1, 10);

        for ($i = 0; $i < $totalSeedCalled; $i++) {
            $this->seed(RoleSeeder::class);
        }


        // Assert
        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseHas('roles', [
            'name' => RoleName::ADMIN->value,
            'guard_name' => $this->mockedGuardName,
        ]);
        $this->assertDatabaseHas('roles', [
            'name' => RoleName::NORMAL->value,
            'guard_name' => $this->mockedGuardName,
        ]);
    }

    #[Test]
    public function should_reseed_roles_after_default_guard_is_change(): void
    {
        // Arrange
        $totalSeedCalled = $this->faker->numberBetween(1, 10);

        for ($i = 0; $i < $totalSeedCalled; $i++) {
            Config::set(self::DEFAULT_GUARD_NAME, $this->faker->unique()->word());
            $this->seed(RoleSeeder::class);
        }


        // Assert
        $totalRoles = 2 * $totalSeedCalled;
        $this->assertDatabaseCount('roles', $totalRoles);
    }
}
