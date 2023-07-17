<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Logger\Message\LoggerMessageFactory;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use Illuminate\Http\Request;
use Tests\Unit\Providers\CoreBinder\Dependencies\DependencyFactoryRaw;

class CoreBinderTestCaseLogger extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            LoggerMessageFactoryContract::class => [
                LoggerMessageFactory::class,
                [
                    new DependencyFactoryRaw(new Request()),
                ],
            ],
        ];
    }
}
