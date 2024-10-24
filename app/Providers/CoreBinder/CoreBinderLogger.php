<?php

declare(strict_types=1);

namespace App\Providers\CoreBinder;

use App\Services\Logger\Logger;
use App\Services\Logger\LoggerLaravel;
use App\Services\Logger\Message\LogMessageDirector;
use App\Services\Logger\Message\LogMessageDirectorContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class CoreBinderLogger implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(
            Logger::class,
            fn (Application $app) => new LoggerLaravel(),
        );

        $app->bind(
            LogMessageDirectorContract::class,
            fn (Application $app) => new LogMessageDirector(
                $app->make(Request::class),
            ),
        );
    }
}
