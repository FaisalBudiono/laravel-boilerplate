<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Logger\Message\LoggerMessageFactory;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Core\Logger\Message\LogMessageBuilder;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirector;
use App\Core\Logger\Message\LogMessageDirectorContract;
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
            LogMessageBuilderContract::class => [
                LogMessageBuilder::class,
            ],
            LogMessageDirectorContract::class => [
                LogMessageDirector::class,
                [
                    new DependencyFactoryRaw(new Request()),
                ],
            ],
        ];
    }
}
