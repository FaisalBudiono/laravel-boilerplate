<?php

declare(strict_types=1);

namespace App\Core\Formatter\ExceptionMessage;

use App\Core\Formatter\ExceptionErrorCode;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExceptionMessageGenericTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageGeneric();


        // Assert
        $this->assertInstanceOf(ExceptionMessage::class, $exceptionMessage);
    }

    #[Test]
    public function getJsonResponse_should_return_generic_json_response(): void
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageGeneric();


        // Act
        $result = $exceptionMessage->getJsonResponse();


        // Assert
        $this->assertEquals(collect([
            'message' => 'Something Wrong on Our Server',
            'errorCode' => ExceptionErrorCode::GENERIC->value,
            'meta' => [],
        ]), $result);
    }

    #[Test]
    public function getMessage_should_return_generic_message(): void
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageGeneric();


        // Act
        $result = $exceptionMessage->getMessage();


        // Assert
        $this->assertEquals('Something Wrong on Our Server', $result);
    }
}
