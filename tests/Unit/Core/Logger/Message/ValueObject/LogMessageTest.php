<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\ValueObject;

use App\Core\Logger\Message\ProcessingStatus;
use App\Core\Logger\Message\ValueObject\LogMessage;
use PHPUnit\Framework\Attributes\Test;
use Stringable;
use Tests\TestCase;

class LogMessageTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(Stringable::class, $this->makeVO());
    }

    #[Test]
    public function toArray_should_map_data_correctly(): void
    {
        // Arrange
        $endpoint = $this->faker->sentence();
        $requestID = $this->faker->sentence();
        $processingStatus = $this->faker->randomElement(
            ProcessingStatus::cases(),
        );
        $message = $this->faker->sentence();
        $meta = $this->faker->sentences();


        // Act
        $result = $this->makeVO(
            $endpoint,
            $requestID,
            $processingStatus,
            $message,
            $meta,
        )->toArray();


        // Assert
        $this->assertEquals([
            'endpoint' => $endpoint,
            'request-id' => $requestID,
            'processing-status' => $processingStatus->value,
            'message' => $message,
            'meta' => $meta,
        ], $result);
    }

    #[Test]
    public function toString_should_JSONified_data_from_toArray(): void
    {
        // Arrange
        $vo = $this->makeVO();


        // Act
        $result = $vo->__toString();


        // Assert
        $this->assertEquals(json_encode($vo->toArray()), $result);
    }

    protected function makeVO(
        ?string $endpoint = null,
        ?string $requestID = null,
        ?ProcessingStatus $processingStatus = null,
        ?string $message = null,
        ?array $meta = null,
    ): LogMessage {
        return new LogMessage(
            $endpoint ?? $this->faker->sentence(),
            $requestID ?? $this->faker->sentence(),
            $processingStatus ?? $this->faker->randomElement(
                ProcessingStatus::cases(),
            ),
            $message ?? $this->faker->sentence(),
            $meta ?? $this->faker->sentences(),
        );
    }
}
