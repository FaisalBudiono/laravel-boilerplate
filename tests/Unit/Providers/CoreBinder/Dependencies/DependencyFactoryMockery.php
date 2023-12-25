<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\CoreBinder\Dependencies;

use Tests\TestCase;
use Tests\Unit\Providers\CoreServiceProviderTest;

class DependencyFactoryMockery extends TestCase implements DependencyFactory
{
    public function __construct(
        protected CoreServiceProviderTest $test,
        protected string $className,
    ) {
    }

    public function make(): mixed
    {
        return $this->test->mock($this->className);
    }
}
