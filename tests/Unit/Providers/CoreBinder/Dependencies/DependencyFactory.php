<?php

declare(strict_types=1);

namespace Tests\Unit\Providers\CoreBinder\Dependencies;

interface DependencyFactory
{
    public function make(): mixed;
}
