<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Services\User\UserService;
use App\Services\User\UserServiceContract;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderUser implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            UserServiceContract::class,
            fn (Application $app) => new UserService(),
        );
    }
}
