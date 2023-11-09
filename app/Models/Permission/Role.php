<?php

namespace App\Models\Permission;

use App\Models\Permission\Enum\RoleName;
use Carbon\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property RoleName $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Role extends SpatieRole
{
    protected $casts = [
        'name' => RoleName::class,
    ];
}
