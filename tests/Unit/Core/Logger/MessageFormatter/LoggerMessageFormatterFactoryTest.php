<?php

namespace Tests\Unit\Core\Logger\MessageFormatter;

use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactory;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterGeneric;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoggerMessageFormatterFactoryTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface()
    {
        // Assert
        $this->assertInstanceOf(
            LoggerMessageFormatterFactoryContract::class,
            $this->makeService()
        );
    }

    #[Test]
    public function makeGeneric_should_return_generic_message_formatter()
    {
        // Arrange
        $endpoint = $this->faker->sentence();
        $requestID = $this->faker->sentence();
        $processingStatus = $this->faker->randomElement(ProcessingStatus::cases());
        $message = $this->faker->sentence();
        $meta = $this->faker->sentences();

        $service = $this->makeService();


        // Act
        $result = $service->makeGeneric(
            $endpoint,
            $requestID,
            $processingStatus,
            $message,
            $meta,
        );


        // Assert
        $expectedResult = new LoggerMessageFormatterGeneric(
            $endpoint,
            $requestID,
            $processingStatus,
            $message,
            $meta,
        );
        $this->assertEquals($expectedResult, $result);
    }

    protected function makeService(): LoggerMessageFormatterFactory
    {
        return new LoggerMessageFormatterFactory;
    }
}
