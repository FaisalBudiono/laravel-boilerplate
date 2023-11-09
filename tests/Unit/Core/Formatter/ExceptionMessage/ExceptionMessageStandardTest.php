<?php

namespace App\Core\Formatter\ExceptionMessage;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExceptionMessageStandardTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageStandard('some-error', 'some-const');


        // Assert
        $this->assertInstanceOf(ExceptionMessage::class, $exceptionMessage);
    }

    #[Test]
    public function getJsonResponse_should_return_formatted_json_response_when_constructor_is_completely_filled(): void
    {
        // Arrange
        $message = $this->faker->words(3, true);
        $errorCode = $this->faker->word();
        $meta = Collection::times(4)->mapWithKeys(fn () => [
            $this->faker->word => $this->faker->words(),
        ])->toArray();


        // Act
        $exceptionMessage = new ExceptionMessageStandard(
            $message,
            $errorCode,
            $meta,
        );
        $result = $exceptionMessage->getJsonResponse();


        // Assert
        $this->assertEqualsCanonicalizing(collect([
            'message' => $message,
            'errorCode' => $errorCode,
            'meta' => $meta,
        ]), $result);
    }

    #[Test]
    public function getJsonResponse_should_return_formatted_json_response_when_created_without_meta(): void
    {
        // Arrange
        $message = $this->faker->words(3, true);
        $errorCode = $this->faker->word();


        // Act
        $exceptionMessage = new ExceptionMessageStandard(
            $message,
            $errorCode,
        );
        $result = $exceptionMessage->getJsonResponse();


        // Assert
        $this->assertEqualsCanonicalizing(collect([
            'message' => $message,
            'errorCode' => $errorCode,
            'meta' => [],
        ]), $result);
    }

    #[Test]
    public function getMessage_should_return_message(): void
    {
        // Arrange
        $message = $this->faker->words(3, true);


        // Act
        $exceptionMessage = new ExceptionMessageStandard($message, 'some-const');
        $result = $exceptionMessage->getMessage();


        // Assert
        $this->assertSame($message, $result);
    }
}
