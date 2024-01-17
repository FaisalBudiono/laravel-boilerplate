<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\Enum\LogEndpoint;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\ProcessingStatus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class LogMessageDirector_BuildQueue_Test extends LogMessageDirectorBaseTestCase
{
    #[Test]
    public function should_return_builder_correctly(): void
    {
        // Arrange
        $mockedProcessingStatus = $this->faker->randomElement(ProcessingStatus::cases());
        $mockedRequestID = $this->faker->uuid();
        $mockedClassname = $this->faker->word();

        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedRequestID,
                $mockedClassname,
                $mockedProcessingStatus,
            ) {
                $mock->shouldReceive('endpoint')->once()->with(LogEndpoint::QUEUE->value)->andReturn($mock);
                $mock->shouldReceive('requestID')->once()->with($mockedRequestID)->andReturn($mock);
                $mock->shouldReceive('message')->once()->with($mockedClassname)->andReturn($mock);
                $mock->shouldReceive('processingStatus')->once()->with($mockedProcessingStatus)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService()->buildQueue(
            $mockLogBuilder,
            $mockedProcessingStatus,
            $mockedClassname,
            $mockedRequestID,
        );


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }
}
