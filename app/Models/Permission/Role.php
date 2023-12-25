<?php

declare(strict_types=1);

namespace App\Models\Permission;

use App\Models\Permission\Enum\RoleName;
use Carbon\Carbon;
use Database\Factories\Permission\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;

    protected $casts = [
        'name' => RoleName::class,
    ];

    protected static function newFactory()
    {
        return RoleFactory::new();
    }
}
