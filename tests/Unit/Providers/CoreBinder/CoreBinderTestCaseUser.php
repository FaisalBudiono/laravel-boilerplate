<?php

namespace Tests\Unit\Providers\CoreBinder;

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
        ];
    }
}