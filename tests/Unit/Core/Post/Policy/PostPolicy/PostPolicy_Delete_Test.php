<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\Policy\PostPolicy;

class PostPolicy_Delete_Test extends PostPolicyOwnerTestCase
{
    protected function methodName(): string
    {
        return 'delete';
    }
}
