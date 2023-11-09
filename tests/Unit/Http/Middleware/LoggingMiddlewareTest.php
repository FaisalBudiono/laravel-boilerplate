<?php

namespace Tests\Unit\Http\Middleware;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\UnauthorizedException;
use App\Http\Middleware\LoggingMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
    ): void {
        // Arrange
        $mockedMethod = $this->faker->randomElement(self::getMethods());
        $mockedURL = $this->faker->url();

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use ($mockedMethod, $mockedURL, $mockedInput) {
                $mock->shouldReceive('all')
                    ->atLeast()
                    ->once()
                    ->andReturn($mockedInput);

                $mock->shouldReceive('method')
                    ->atLeast()
                    ->once()
                    ->andReturn($mockedMethod);

                $mock->shouldReceive('url')
                    ->atLeast()
                    ->once()
                    ->andReturn($mockedURL);
            }
        );
        assert($mockRequest instanceof Request);

        $mockedStartLog = $this->faker->sentence();
        $mockedSuccessLog = $this->faker->sentence();

        $mockLoggerMessageFactory = $this->mock(
            LoggerMessageFactoryContract::class,
            function (MockInterface $mock) use (
                $mockedStartLog,
                $mockedSuccessLog,
                $mockedMethod,
                $mockedURL,
                $expectedInput,
            ) {
                $mock->shouldReceive('makeHTTPStart')
                    ->once()
                    ->with("{$mockedMethod} {$mockedURL}", $expectedInput)
                    ->andReturn(
                        $this->mock(
                            \Stringable::class,
                            function (MockInterface $mock) use ($mockedStartLog) {
                                $mock->shouldReceive('__toString')->andReturn($mockedStartLog);
                            }
                        )
                    );

                $mock->shouldReceive('makeHTTPSuccess')
                    ->once()
                    ->with("{$mockedMethod} {$mockedURL}", [])
                    ->andReturn(
                        $this->mock(
                            \Stringable::class,
                            function (MockInterface $mock) use ($mockedSuccessLog) {
                                $mock->shouldReceive('__toString')->andReturn($mockedSuccessLog);
                            }
                        )
                    );
            }
        );
        assert($mockLoggerMessageFactory instanceof LoggerMessageFactoryContract);

        $middleware = $this->makeMiddleware($mockLoggerMessageFactory);


        // Pre-Assert
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($argMessage) use ($mockedStartLog) {
                try {
                    $this->assertEquals($mockedStartLog, $argMessage);
                    return true;
                } catch (\Exception $e) {
                    dd($e);
                }
            });
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($argMessage) use ($mockedSuccessLog) {
                try {
                    $this->assertEquals($mockedSuccessLog, $argMessage);
                    return true;
                } catch (\Exception $e) {
                    dd($e);
                }
            });


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
    ): void {
        // Arrange
        $mockedMethod = $this->faker->randomElement(self::getMethods());
        $mockedURL = $this->faker->url();
        $mockedInput = [
            'foo' => 'bar',
        ];

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use ($mockedMethod, $mockedURL, $mockedInput) {
                $mock->shouldReceive('all')
                    ->atLeast()
                    ->once()
                    ->andReturn($mockedInput);

                $mock->shouldReceive('method')
                    ->atLeast()
                    ->once()
                    ->andReturn($mockedMethod);

                $mock->shouldReceive('url')
                    ->atLeast()
                    ->once()
                    ->andReturn($mockedURL);
            }
        );
        assert($mockRequest instanceof Request);

        $mockedStartLog = $this->faker->sentence();
        $mockedErrorLog = $this->faker->sentence();

        $mockLoggerMessageFactory = $this->mock(
            LoggerMessageFactoryContract::class,
            function (MockInterface $mock) use (
                $mockedStartLog,
                $mockedErrorLog,
                $mockedMethod,
                $mockedURL,
                $mockedInput,
                $expectedPreviousException,
            ) {
                $mock->shouldReceive('makeHTTPStart')
                    ->once()
                    ->with("{$mockedMethod} {$mockedURL}", $mockedInput)
                    ->andReturn(
                        $this->mock(
                            \Stringable::class,
                            function (MockInterface $mock) use ($mockedStartLog) {
                                $mock->shouldReceive('__toString')->andReturn($mockedStartLog);
                            }
                        )
                    );

                $mock->shouldReceive('makeHTTPError')
                    ->once()
                    ->withArgs(function (\Throwable $argException) use ($expectedPreviousException) {
                        try {
                            $this->assertSame($expectedPreviousException, $argException);
                            return true;
                        } catch (\Throwable $e) {
                            dd($e);
                        }
                    })->andReturn(
                        $this->mock(
                            \Stringable::class,
                            function (MockInterface $mock) use ($mockedErrorLog) {
                                $mock->shouldReceive('__toString')->andReturn($mockedErrorLog);
                            }
                        )
                    );
            }
        );
        assert($mockLoggerMessageFactory instanceof LoggerMessageFactoryContract);

        $middleware = $this->makeMiddleware($mockLoggerMessageFactory);


        // Pre-Assert
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($argMessage) use ($mockedStartLog) {
                try {
                    $this->assertEquals($mockedStartLog, $argMessage);
                    return true;
                } catch (\Exception $e) {
                    dd($e);
                }
            });
        Log::shouldReceive($expectedErrorLogLevel)
            ->once()
            ->withArgs(function ($argMessage) use ($mockedErrorLog) {
                try {
                    $this->assertEquals($mockedErrorLog, $argMessage);
                    return true;
                } catch (\Exception $e) {
                    dd($e);
                }
            });


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
            new ExceptionMessageGeneric
        );
        $unauthorizedWithoutPrevious = new UnauthorizedException(
            new ExceptionMessageGeneric
        );

        return [
            '500++ exception (internal server error)' => [
                new InternalServerErrorException(new ExceptionMessageGeneric, $previousException),
                $previousException,
                'error',
            ],
            '500++ exception without previous exception (internal server error)' => [
                $internalServerErrorWithoutPrevious,
                $internalServerErrorWithoutPrevious,
                'error',
            ],

            '400-499 exception (unauthorized)' => [
                new UnauthorizedException(new ExceptionMessageGeneric, $previousException),
                $previousException,
                'warning',
            ],
            '400-499 exception (conflict)' => [
                new ConflictException(new ExceptionMessageGeneric, $previousException),
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
        ?LoggerMessageFactoryContract $loggerMessageFactory = null,
    ): LoggingMiddleware {
        if (is_null($loggerMessageFactory)) {
            $loggerMessageFactory = $this->mock(LoggerMessageFactoryContract::class);
        }

        return new LoggingMiddleware($loggerMessageFactory);
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
