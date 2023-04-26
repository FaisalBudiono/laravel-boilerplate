<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactory;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;

class CoreBinderTestCaseLogger extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            LoggerMessageFormatterFactoryContract::class => [
                LoggerMessageFormatterFactory::class,
            ],
        ];
    }
}
