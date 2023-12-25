<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\CoreBinder\Dependencies;

use Tests\TestCase;

class DependencyFactoryRaw extends TestCase implements DependencyFactory
{
    public function __construct(
        protected mixed $value,
    ) {
    }

    public function make(): mixed
    {
        return $this->value;
    }
}
