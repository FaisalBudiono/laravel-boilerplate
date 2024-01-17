<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\ProcessingStatus;

class LogMessageDirector_BuildError_Test extends LogMessageDirectorHTTPScenario
{
    protected function methodName(): string
    {
        return 'buildError';
    }

    protected function processingStatus(): ProcessingStatus
    {
        return ProcessingStatus::ERROR;
    }
}
