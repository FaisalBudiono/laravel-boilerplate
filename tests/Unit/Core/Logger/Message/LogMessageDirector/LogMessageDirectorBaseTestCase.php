<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\LogMessageDirector;
use Illuminate\Http\Request;
use Tests\TestCase;

abstract class LogMessageDirectorBaseTestCase extends TestCase
{
    protected function makeService(
        ?Request $request = null,
    ): LogMessageDirector {
        return new LogMessageDirector(
            $request ?? new Request(),
        );
    }
}
