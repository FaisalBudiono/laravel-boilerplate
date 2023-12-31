<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\Post\Policy\PostPolicy;
use App\Core\Post\Policy\PostPolicyContract;
use App\Core\Post\PostCore;
use App\Core\Post\PostCoreContract;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderPost implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            PostCoreContract::class,
            fn (Application $app) => new PostCore(),
        );

        $app->bind(
            PostPolicyContract::class,
            fn (Application $app) => new PostPolicy(),
        );
    }
}
