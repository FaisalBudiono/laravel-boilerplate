<?php

namespace Tests\Unit\Core\Logger\MessageFormatter;

use App\Core\Logger\MessageFormatter\LoggerMessageFormatter;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterGeneric;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoggerMessageFormatterGenericTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface()
    {
        // Arrange
        $service = new LoggerMessageFormatterGeneric(
            '',
            '',
            ProcessingStatus::BEGIN,
            '',
            [],
        );


        // Assert
        $this->assertInstanceOf(LoggerMessageFormatter::class, $service);
    }

    #[Test]
    public function should_format_logging_message_to_string_json_with_complete_data()
    {
        // Arrange
        $endpoint = $this->faker()->sentence;
        $requestId = $this->faker()->uuid;
        /** @var ProcessingStatus */
        $processingStatus = $this->faker()->randomElement(ProcessingStatus::cases());
        $message = $this->faker()->sentence;
        $meta = $this->faker()->sentences();

        $service = new LoggerMessageFormatterGeneric(
            $endpoint,
            $requestId,
            $processingStatus,
            $message,
            $meta,
        );


        // Act
        $result = $service->getMessage();


        // Assert
        $this->assertJson($result);

        $arrayedResult = json_decode($result, true);

        $this->assertArrayHasKey('endpoint', $arrayedResult);
        $this->assertSame($endpoint, $arrayedResult['endpoint']);

        $this->assertArrayHasKey('request-id', $arrayedResult);
        $this->assertSame($requestId, $arrayedResult['request-id']);

        $this->assertArrayHasKey('processing-status', $arrayedResult);
        $this->assertSame($processingStatus->value, $arrayedResult['processing-status']);

        $this->assertArrayHasKey('message', $arrayedResult);
        $this->assertSame($message, $arrayedResult['message']);

        $this->assertArrayHasKey('meta', $arrayedResult);
        $this->assertSame($meta, $arrayedResult['meta']);
    }
}
