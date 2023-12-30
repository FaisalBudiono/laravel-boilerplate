<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirector;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Core\Logger\Message\ProcessingStatus;
use App\Exceptions\Core\Auth\InvalidCredentialException;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\UnprocessableEntityException;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogMessageDirectorTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(LogMessageDirectorContract::class, $this->makeService());
    }

    #[Test]
    #[DataProvider('processingTypeDataProvider')]
    public function should_return_builder_correctly_by_processing(
        string $methodName,
        ProcessingStatus $processingStatus,
    ): void {
        // Arrange
        $mockedUUID = $this->faker->uuid();
        $mockedURL = $this->faker->url();
        $mockedMethod = $this->faker->word();

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use (
                $mockedUUID,
                $mockedURL,
                $mockedMethod,
            ) {
                $mock->shouldReceive('header')->once()->with(XRequestIDMiddleware::HEADER_NAME)->andReturn($mockedUUID);
                $mock->shouldReceive('url')->once()->withNoArgs()->andReturn($mockedURL);
                $mock->shouldReceive('method')->once()->withNoArgs()->andReturn($mockedMethod);
            }
        );
        assert($mockRequest instanceof Request);

        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedUUID,
                $mockedURL,
                $mockedMethod,
                $processingStatus,
            ) {
                $mock->shouldReceive('requestID')->once()->with($mockedUUID)->andReturn($mock);
                $mock->shouldReceive('endpoint')->once()->with("{$mockedMethod} {$mockedURL}")->andReturn($mock);
                $mock->shouldReceive('processingStatus')->once()->with($processingStatus)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService($mockRequest)->{$methodName}($mockLogBuilder);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }

    public static function processingTypeDataProvider(): array
    {
        return [
            'method buildBegin' => [
                'buildBegin',
                ProcessingStatus::BEGIN,
            ],
            'method buildProcessing' => [
                'buildProcessing',
                ProcessingStatus::PROCESSING,
            ],
            'method buildSuccess' => [
                'buildSuccess',
                ProcessingStatus::SUCCESS,
            ],
            'method buildError' => [
                'buildError',
                ProcessingStatus::ERROR,
            ],
        ];
    }

    #[Test]
    #[DataProvider('unusualRequestIdDataProvider')]
    public function should_handle_not_string_request_id_by_request_gracefully(
        mixed $headerResult,
        string $expectedRequestID,
    ): void {
        // Arrange
        $mockedURL = $this->faker->url();
        $mockedMethod = $this->faker->word();

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use (
                $mockedURL,
                $mockedMethod,
                $headerResult,
            ) {
                $mock->shouldReceive('header')->once()->with(XRequestIDMiddleware::HEADER_NAME)->andReturn($headerResult);
                $mock->shouldReceive('url')->once()->withNoArgs()->andReturn($mockedURL);
                $mock->shouldReceive('method')->once()->withNoArgs()->andReturn($mockedMethod);
            }
        );
        assert($mockRequest instanceof Request);

        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedURL,
                $mockedMethod,
                $expectedRequestID,
            ) {
                $mock->shouldReceive('requestID')->once()->with($expectedRequestID)->andReturn($mock);
                $mock->shouldReceive('endpoint')->once()->with("{$mockedMethod} {$mockedURL}")->andReturn($mock);
                $mock->shouldReceive('processingStatus')->once()->with(ProcessingStatus::BEGIN)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService($mockRequest)->buildBegin($mockLogBuilder);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }

    public static function unusualRequestIdDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'null' => [
                null,
                '',
            ],
            'array' => [
                $headers = $faker->sentences(),
                implode(' ', $headers),
            ],
        ];
    }

    #[Test]
    public function buildEndpointHTTP_should_return_builder_correctly(): void
    {
        // Arrange
        $mockedURL = $this->faker->url();
        $mockedMethod = $this->faker->word();

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use (
                $mockedURL,
                $mockedMethod,
            ) {
                $mock->shouldReceive('url')->once()->withNoArgs()->andReturn($mockedURL);
                $mock->shouldReceive('method')->once()->withNoArgs()->andReturn($mockedMethod);
            }
        );
        assert($mockRequest instanceof Request);

        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedURL,
                $mockedMethod,
            ) {
                $mock->shouldReceive('endpoint')->once()->with("{$mockedMethod} {$mockedURL}")->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService($mockRequest)->buildEndpointHTTP($mockLogBuilder);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }

    #[Test]
    #[DataProvider('exceptionDataProvider')]
    public function buildForException_should_return_builder_correctly(
        \Throwable $mockedException,
        array $expectedMeta,
    ): void {
        // Arrange
        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use ($mockedException, $expectedMeta) {
                $mock->shouldReceive('message')->once()->with($mockedException->getMessage())->andReturn($mock);
                $mock->shouldReceive('meta')->once()->with($expectedMeta)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService()->buildForException($mockLogBuilder, $mockedException);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }

    public static function exceptionDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'generic exception' => [
                $e = new \Error($faker->sentence()),
                [
                    'detail' => null,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ],
            ],
            'HTTPException' => [
                $e = new UnprocessableEntityException(new ExceptionMessageStandard(
                    $faker->sentence(),
                    $faker->sentence(),
                )),
                [
                    'detail' => null,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ],
            ],
            'BaseException' => [
                $e = new InvalidCredentialException(new ExceptionMessageStandard(
                    $faker->sentence(),
                    $faker->sentence(),
                )),
                [
                    'detail' => $e->exceptionMessage->getJsonResponse()->toArray(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ],
            ],
        ];
    }

    protected function makeService(
        ?Request $request = null,
    ): LogMessageDirector {
        return new LogMessageDirector(
            $request ?? new Request(),
        );
    }
}
