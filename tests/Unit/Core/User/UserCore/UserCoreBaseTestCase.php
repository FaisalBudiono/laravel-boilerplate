<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\UserCore;

use App\Core\User\UserCore;
use Tests\TestCase;

abstract class UserCoreBaseTestCase extends TestCase
{
    protected function makeService(): UserCore
    {
        return new UserCore();
    }
}
