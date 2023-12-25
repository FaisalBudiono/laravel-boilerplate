<?php

declare(strict_types=1);

namespace App\Models\Permission;

use Carbon\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Permission extends SpatiePermission
{
}
