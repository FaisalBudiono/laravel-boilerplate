<?php

declare(strict_types=1);

namespace Database\Seeders\Base;

use App\Models\Permission\Enum\RoleName;
use App\Models\Permission\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getUpsertFormattedRoles() as $upsertRole) {
            Role::updateOrCreate(...$upsertRole);
        }
    }

    protected function formatForUpsert(array $role): Collection
    {
        return collect([
            [
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
            ],
            [
                ...$role,
            ],
        ]);
    }

    protected function getDefaultGuardName(): string
    {
        return config('auth.defaults.guard', '');
    }

    protected function getUpsertFormattedRoles(): Collection
    {
        return $this->getRoleSeedInfos()->map(
            fn (array $role) => $this->formatForUpsert($role)
        );
    }

    protected function getRoleSeedInfos(): Collection
    {
        return collect([
            [
                'name' => RoleName::ADMIN->value,
                'guard_name' => $this->getDefaultGuardName(),
            ],
            [
                'name' => RoleName::NORMAL->value,
                'guard_name' => $this->getDefaultGuardName(),
            ],
        ]);
    }
}
