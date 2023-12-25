<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Post\PostCore;
use App\Core\Post\PostCoreContract;

class CoreBinderTestCasePost extends CoreBinderTestCaseAbstract
{
    protected function abstractWithImplementationList(): array
    {
        return [
            PostCoreContract::class => [
                PostCore::class,
            ],
        ];
    }
}
