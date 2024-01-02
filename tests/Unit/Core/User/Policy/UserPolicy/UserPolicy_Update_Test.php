<?php

declare(strict_types=1);

namespace Tests\Unit\Core\User\Policy\UserPolicy;

class UserPolicy_Update_Test extends UserPolicySameTestCase
{
    protected function methodName(): string
    {
        return 'update';
    }
}
