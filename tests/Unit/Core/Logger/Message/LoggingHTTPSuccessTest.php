<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message;

use App\Core\Logger\Message\LoggingHTTPSuccess;
use App\Core\Logger\Message\ProcessingStatus;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stringable;
use Tests\TestCase;

class LoggingHTTPSuccessTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Arrange
        $service = new LoggingHTTPSuccess(
            new Request(),
            $this->faker->sentence,
            $this->faker->sentences(),
        );


        // Assert
        $this->assertInstanceOf(Stringable::class, $service);
    }

    #[Test]
    public function should_format_logging_message_to_string_json_with_complete_data(): void
    {
        // Arrange
        $mockedURL = $this->faker->url();
        $mockedRequestID = $this->faker->uuid();

        $message = $this->faker()->sentence;
        $meta = $this->faker()->sentences();


        // Assert
        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use ($mockedURL, $mockedRequestID) {
                $mock->shouldReceive('url')
                    ->once()
                    ->andReturn($mockedURL);

                $mock->shouldReceive('header')
                    ->once()
                    ->with(XRequestIDMiddleware::HEADER_NAME)
                    ->andReturn($mockedRequestID);
            }
        );
        assert($mockRequest instanceof Request);


        // Act
        $service = new LoggingHTTPSuccess(
            $mockRequest,
            $message,
            $meta,
        );

        $result = $service->__toString();


        // Assert
        $this->assertJson($result);

        $arrayedResult = json_decode($result, true);

        $this->assertArrayHasKey('endpoint', $arrayedResult);
        $this->assertSame($mockedURL, $arrayedResult['endpoint']);

        $this->assertArrayHasKey('request-id', $arrayedResult);
        $this->assertSame($mockedRequestID, $arrayedResult['request-id']);

        $this->assertArrayHasKey('processing-status', $arrayedResult);
        $this->assertSame(ProcessingStatus::SUCCESS->value, $arrayedResult['processing-status']);

        $this->assertArrayHasKey('message', $arrayedResult);
        $this->assertSame($message, $arrayedResult['message']);

        $this->assertArrayHasKey('meta', $arrayedResult);
        $this->assertSame($meta, $arrayedResult['meta']);
    }
}
