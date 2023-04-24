<?php

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Formatter\Randomizer\RandomizerUUID;

class CoreBinderTestCaseFormatter extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            Randomizer::class => [
                RandomizerUUID::class,
            ],
        ];
    }
}
