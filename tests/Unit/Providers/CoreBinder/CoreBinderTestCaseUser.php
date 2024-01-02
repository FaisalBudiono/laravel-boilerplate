<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\User\Policy\UserPolicy;
use App\Core\User\Policy\UserPolicyContract;
use App\Core\User\UserCore;
use App\Core\User\UserCoreContract;

class CoreBinderTestCaseUser extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            UserCoreContract::class => [
                UserCore::class,
            ],
            UserPolicyContract::class => [
                UserPolicy::class,
            ],
        ];
    }
}
