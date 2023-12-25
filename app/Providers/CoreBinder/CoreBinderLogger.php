<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Core\Logger\Message\LoggerMessageFactory;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class CoreBinderLogger implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            LoggerMessageFactoryContract::class,
            fn (Application $app) => new LoggerMessageFactory(
                $app->make(Request::class),
            )
        );
    }
}
