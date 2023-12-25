<?php

declare(strict_types=1);

namespace Tests\Helper\Enum;

enum TestBackedEnumString: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
