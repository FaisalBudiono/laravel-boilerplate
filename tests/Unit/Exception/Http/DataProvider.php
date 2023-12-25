<?php

declare(strict_types=1);

namespace Tests\Unit\Exception\Http;

use Exception;
use RuntimeException;

class DataProvider
{
    public static function previousExceptions(): array
    {
        return [
            'null' => [null],
            'Exception' => [new Exception('some error')],
            'RuntimeException' => [new RuntimeException('some error')],
        ];
    }
}
