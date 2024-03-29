<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\User\Policy\UserPolicy;
use App\Core\User\Policy\UserPolicyContract;
use App\Core\User\UserCore;
use App\Core\User\UserCoreContract;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderUser implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            UserCoreContract::class,
            fn (Application $app) => new UserCore(),
        );

        $app->bind(
            UserPolicyContract::class,
            fn (Application $app) => new UserPolicy(),
        );
    }
}
