<?php

namespace App\Providers\CoreBinder;

use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactory;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use Illuminate\Contracts\Foundation\Application;

class CoreBinderLogger implements CoreBinder
{
    public function bootCore(Application $app): void
    {
        $app->bind(LoggerMessageFormatterFactoryContract::class, function (Application $app) {
            return new LoggerMessageFormatterFactory;
        });
    }
}
