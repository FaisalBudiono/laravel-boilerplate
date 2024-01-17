<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\ProcessingStatus;

class LogMessageDirector_BuildSuccess_Test extends LogMessageDirectorHTTPScenario
{
    protected function methodName(): string
    {
        return 'buildSuccess';
    }

    protected function processingStatus(): ProcessingStatus
    {
        return ProcessingStatus::SUCCESS;
    }
}
