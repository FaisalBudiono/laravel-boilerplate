<?php

declare(strict_types=1);

namespace Tests\Helper\Enum;

enum TestBackedEnumInt: int
{
    case FOO = 1;
    case BAR = 102;
}
