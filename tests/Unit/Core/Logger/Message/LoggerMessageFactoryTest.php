<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message;

use App\Core\Logger\Message\LoggerMessageFactory;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Core\Logger\Message\LoggingHTTPError;
use App\Core\Logger\Message\LoggingHTTPStart;
use App\Core\Logger\Message\LoggingHTTPSuccess;
use Exception;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoggerMessageFactoryTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(LoggerMessageFactoryContract::class, $this->makeService());
    }

    #[Test]
    public function makeHTTPError_should_return_http_error_logger_formatter(): void
    {
        // Arrange
        $request = $this->mock(Request::class);
        assert($request instanceof Request);

        $mockedException = new Exception($this->faker->sentence);


        // Act
        $result = $this->makeService()->makeHTTPError($mockedException);


        // Assert
        $expectedResult = new LoggingHTTPError(
            $request,
            $mockedException->getMessage(),
            [
                'trace' => $mockedException->getTrace(),
            ],
        );
        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function makeHTTPStart_should_return_http_error_logger_formatter(): void
    {
        // Arrange
        $request = $this->mock(Request::class);
        assert($request instanceof Request);

        $message = $this->faker->sentence;
        $input = $this->faker->sentences();


        // Act
        $result = $this->makeService()->makeHTTPStart($message, $input);


        // Assert
        $expectedResult = new LoggingHTTPStart($request, $message, [
            'input' => $input,
        ]);
        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function makeHTTPSuccess_should_return_http_error_logger_formatter(): void
    {
        // Arrange
        $request = $this->mock(Request::class);
        assert($request instanceof Request);

        $message = $this->faker->sentence;
        $meta = $this->faker->sentences();


        // Act
        $result = $this->makeService()->makeHTTPSuccess($message, $meta);


        // Assert
        $expectedResult = new LoggingHTTPSuccess($request, $message, $meta);
        $this->assertEquals($expectedResult, $result);
    }

    protected function makeService(
        ?Request $request = null,
    ): LoggerMessageFactory {
        if (is_null($request)) {
            $request = $this->mock(Request::class);
        }

        return new LoggerMessageFactory($request);
    }
}
