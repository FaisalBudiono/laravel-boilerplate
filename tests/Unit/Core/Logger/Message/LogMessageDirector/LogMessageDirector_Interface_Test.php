<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\LogMessageDirectorContract;
use PHPUnit\Framework\Attributes\Test;

class LogMessageDirector_Interface_Test extends LogMessageDirectorBaseTestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(LogMessageDirectorContract::class, $this->makeService());
    }
}
