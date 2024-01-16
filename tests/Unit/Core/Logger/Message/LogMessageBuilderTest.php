<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message;

use App\Core\Logger\Message\LogMessageBuilder;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\ProcessingStatus;
use App\Core\Logger\Message\ValueObject\LogMessage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogMessageBuilderTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(LogMessageBuilderContract::class, $this->makeService());
    }

    #[Test]
    public function should_build_log_message_properly(): void
    {
        // Arrange
        $expectedResult =  new LogMessage(
            $endpoint = $this->faker->sentence(),
            $requestID = $this->faker->sentence(),
            $ip = $this->faker->ipv4(),
            $processingStatus = $this->faker->randomElement(ProcessingStatus::cases()),
            $message = $this->faker->sentence(),
            $meta = $this->faker->sentences(),
        );


        // Assert
        $result = $this->makeService()
            ->endpoint($endpoint)
            ->requestID($requestID)
            ->ip($ip)
            ->processingStatus($processingStatus)
            ->message($message)
            ->meta($meta)
            ->build();


        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function should_build_log_message_properly_with_default_value(): void
    {
        // Arrange
        $expectedResult =  new LogMessage(
            '',
            '',
            '',
            ProcessingStatus::BEGIN,
            '',
            [],
        );


        // Assert
        $result = $this->makeService()->build();


        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    protected function makeService(): LogMessageBuilder
    {
        return new LogMessageBuilder();
    }
}
