<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\ProcessingStatus;

class LogMessageDirector_BuildProcessing_Test extends LogMessageDirectorHTTPScenario
{
    protected function methodName(): string
    {
        return 'buildProcessing';
    }

    protected function processingStatus(): ProcessingStatus
    {
        return ProcessingStatus::PROCESSING;
    }
}
