<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\CoreBinder;

use App\Core\Post\Policy\PostPolicy;
use App\Core\Post\Policy\PostPolicyContract;
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
            PostPolicyContract::class => [
                PostPolicy::class,
            ],
        ];
    }
}
