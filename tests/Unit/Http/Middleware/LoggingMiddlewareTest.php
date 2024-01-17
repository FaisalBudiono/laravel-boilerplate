<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Core\Logger\Message\ProcessingStatus;
use App\Core\Logger\Message\ValueObject\LogMessage;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\UnauthorizedException;
use App\Http\Middleware\LoggingMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helper\MockInstance\Core\Logger\Message\MockerLogMessageDirector;
use Tests\TestCase;

class LoggingMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Log::partialMock();
    }

    #[Test]
    #[DataProvider('inputDataProvider')]
    public function should_successfully_log_before_and_after_response_when_no_exception_thrown(
        array $mockedInput,
        array $expectedInput,
    ) {
        // Arrange
        $mockRequest = $this->mock(
            Request::class,
            function (
                MockInterface $mock,
            ) use ($mockedInput) {
                $mock->shouldReceive('all')->atLeast()->once()->andReturn($mockedInput);
            }
        );
        assert($mockRequest instanceof Request);

        $mockedLogMessage = $this->mock(LogMessage::class);

        $mockLogMessageBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use ($mockedLogMessage, $expectedInput) {
                $mock->shouldReceive('message')->once()->with('start')->andReturn($mock);
                $mock->shouldReceive('message')->once()->with('end')->andReturn($mock);
                $mock->shouldReceive('meta')->once()->with($expectedInput)->andReturn($mock);
                $mock->shouldReceive('build')->twice()->andReturn($mockedLogMessage);
            }
        );
        assert($mockLogMessageBuilder instanceof LogMessageBuilderContract);

        $mockLogMessageDirector = MockerLogMessageDirector::make($this, $mockLogMessageBuilder)
            ->http(ProcessingStatus::BEGIN)
            ->http(ProcessingStatus::SUCCESS)
            ->build();

        $middleware = $this->makeMiddleware(
            $mockLogMessageDirector,
            $mockLogMessageBuilder,
        );


        // Pre-Assert
        Log::shouldReceive('info')
            ->with($mockedLogMessage)
            ->twice();


        // Act
        $middleware->handle(
            $mockRequest,
            function (Request $argRequest) {
                return response()->json();
            }
        );
    }

    public static function inputDataProvider(): array
    {
        return [
            'normal input' => [
                [
                    'foo' => 'bar',
                    'username' => 'some username',
                ],
                [
                    'foo' => 'bar',
                    'username' => 'some username',
                ],
            ],
            'input with password' => [
                [
                    'foo' => 'bar',
                    'username' => 'some username',
                    'password' => 'some password',
                ],
                [
                    'foo' => 'bar',
                    'username' => 'some username',
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider('responseExceptionDataProvider')]
    public function should_successfully_log_before_and_after_response_when_exception_is_thrown(
        \Throwable $mockedResponseException,
        \Throwable $expectedPreviousException,
        string $expectedErrorLogLevel,
    ) {
        // Arrange
        $mockedInput = [
            'foo' => 'bar'
        ];

        $mockRequest = $this->mock(
            Request::class,
            function (
                MockInterface $mock,
            ) use ($mockedInput) {
                $mock->shouldReceive('all')->atLeast()->once()->andReturn($mockedInput);
            }
        );
        assert($mockRequest instanceof Request);

        $mockedLogMessage = $this->mock(LogMessage::class);

        $mockLogMessageBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedLogMessage,
                $mockedInput,
            ) {
                $mock->shouldReceive('message')->once()->with('start')->andReturn($mock);
                $mock->shouldReceive('meta')->once()->with($mockedInput)->andReturn($mock);

                $mock->shouldReceive('build')->twice()->andReturn($mockedLogMessage);
            }
        );
        assert($mockLogMessageBuilder instanceof LogMessageBuilderContract);

        $mockLogMessageDirector = MockerLogMessageDirector::make($this, $mockLogMessageBuilder)
            ->http(ProcessingStatus::BEGIN)
            ->http(ProcessingStatus::ERROR)
            ->forException($expectedPreviousException)
            ->build();

        $middleware = $this->makeMiddleware(
            $mockLogMessageDirector,
            $mockLogMessageBuilder,
        );


        // Pre-Assert
        Log::shouldReceive('info')
            ->with($mockedLogMessage)
            ->once();
        Log::shouldReceive($expectedErrorLogLevel)
            ->with($mockedLogMessage)
            ->once();



        // Act
        $middleware->handle(
            $mockRequest,
            function (Request $argRequest) use ($mockedResponseException) {
                return response()->json()->withException($mockedResponseException);
            }
        );
    }

    public static function responseExceptionDataProvider(): array
    {
        $previousException = new \Error(self::makeFaker()->sentence());

        $internalServerErrorWithoutPrevious = new InternalServerErrorException(
            new ExceptionMessageGeneric()
        );
        $unauthorizedWithoutPrevious = new UnauthorizedException(
            new ExceptionMessageGeneric()
        );

        return [
            '500++ exception (internal server error)' => [
                new InternalServerErrorException(new ExceptionMessageGeneric(), $previousException),
                $previousException,
                'error',
            ],
            '500++ exception without previous exception (internal server error)' => [
                $internalServerErrorWithoutPrevious,
                $internalServerErrorWithoutPrevious,
                'error',
            ],

            '400-499 exception (unauthorized)' => [
                new UnauthorizedException(new ExceptionMessageGeneric(), $previousException),
                $previousException,
                'warning',
            ],
            '400-499 exception (conflict)' => [
                new ConflictException(new ExceptionMessageGeneric(), $previousException),
                $previousException,
                'warning',
            ],
            '400-499 exception without previous exception (unauthorized)' => [
                $unauthorizedWithoutPrevious,
                $unauthorizedWithoutPrevious,
                'warning',
            ],

            'generic exception' => [
                $previousException,
                $previousException,
                'error',
            ],
            'generic exception with less than 400 error code' => [
                $generic399 = new \ValueError(self::makeFaker()->sentence, 399),
                $generic399,
                'error',
            ],
            'generic exception with more than 500 error code' => [
                $generic500 = new \ValueError(self::makeFaker()->sentence, 503),
                $generic500,
                'error',
            ],
            'generic exception with 400ish error code' => [
                $generic400 = new \ValueError(self::makeFaker()->sentence, 405),
                $generic400,
                'warning',
            ],
        ];
    }

    protected function makeMiddleware(
        ?LogMessageDirectorContract $logMessageDirector = null,
        ?LogMessageBuilderContract $logMessageBuilder = null,
    ): LoggingMiddleware {
        return new LoggingMiddleware(
            $logMessageDirector ?? $this->mock(LogMessageDirectorContract::class),
            $logMessageBuilder ?? $this->mock(LogMessageBuilderContract::class),
        );
    }

    protected static function getMethods(): array
    {
        return [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
        ];
    }
}
