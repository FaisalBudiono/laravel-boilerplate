<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\Post\PostCore;
use App\Core\Post\PostCoreContract;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderPost implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            PostCoreContract::class,
            fn (Application $app) => new PostCore()
        );
    }
}
