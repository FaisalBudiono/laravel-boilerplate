<?php

namespace Tests\Unit\Providers\CoreBinder\Dependencies;

interface DependencyFactory
{
    public function make(): mixed;
}
