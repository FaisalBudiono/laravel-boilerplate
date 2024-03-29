<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\ProcessingStatus;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class LogMessageDirector_BuildHTTP_Test extends LogMessageDirectorBaseTestCase
{
    #[Test]
    #[DataProvider('processingStatusDataProvider')]
    public function should_return_builder_correctly_by_processing(
        ProcessingStatus $processingStatus,
    ): void {
        // Arrange
        $mockedUUID = $this->faker->uuid();
        $mockedURL = $this->faker->url();
        $mockedMethod = $this->faker->word();
        $mockedIP = $this->faker->ipv4();

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use (
                $mockedUUID,
                $mockedURL,
                $mockedMethod,
                $mockedIP,
            ) {
                $mock->shouldReceive('header')->once()->with(XRequestIDMiddleware::HEADER_NAME)->andReturn($mockedUUID);
                $mock->shouldReceive('ip')->once()->withNoArgs()->andReturn($mockedIP);
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
                $mockedIP,
                $processingStatus,
            ) {
                $mock->shouldReceive('ip')->once()->with($mockedIP)->andReturn($mock);
                $mock->shouldReceive('requestID')->once()->with($mockedUUID)->andReturn($mock);
                $mock->shouldReceive('endpoint')->once()->with("{$mockedMethod} {$mockedURL}")->andReturn($mock);
                $mock->shouldReceive('processingStatus')->once()->with($processingStatus)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService($mockRequest)->buildHTTP($mockLogBuilder, $processingStatus);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }

    public static function processingStatusDataProvider(): array
    {
        return [
            ProcessingStatus::BEGIN->value => [
                ProcessingStatus::BEGIN,
            ],
            ProcessingStatus::ERROR->value => [
                ProcessingStatus::ERROR,
            ],
            ProcessingStatus::PROCESSING->value => [
                ProcessingStatus::PROCESSING,
            ],
            ProcessingStatus::SUCCESS->value => [
                ProcessingStatus::SUCCESS,
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
        $mockedIP = $this->faker->ipv4();
        $processingStatus = $this->faker->randomElement(ProcessingStatus::cases());

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use (
                $mockedURL,
                $mockedMethod,
                $mockedIP,
                $headerResult,
            ) {
                $mock->shouldReceive('header')->once()->with(XRequestIDMiddleware::HEADER_NAME)->andReturn($headerResult);
                $mock->shouldReceive('ip')->once()->withNoArgs()->andReturn($mockedIP);
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
                $mockedIP,
                $processingStatus,
                $expectedRequestID,
            ) {
                $mock->shouldReceive('ip')->once()->with($mockedIP)->andReturn($mock);
                $mock->shouldReceive('requestID')->once()->with($expectedRequestID)->andReturn($mock);
                $mock->shouldReceive('endpoint')->once()->with("{$mockedMethod} {$mockedURL}")->andReturn($mock);
                $mock->shouldReceive('processingStatus')->once()->with($processingStatus)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService($mockRequest)->buildHTTP($mockLogBuilder, $processingStatus);


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
    public function should_handle_null_ip_in_request_gracefully(): void
    {
        // Arrange
        $mockedURL = $this->faker->url();
        $mockedMethod = $this->faker->word();
        $mockedRequestID = $this->faker->uuid();
        $processingStatus = $this->faker->randomElement(ProcessingStatus::cases());

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use (
                $mockedURL,
                $mockedMethod,
                $mockedRequestID,
            ) {
                $mock->shouldReceive('header')->once()->with(XRequestIDMiddleware::HEADER_NAME)->andReturn($mockedRequestID);
                $mock->shouldReceive('url')->once()->withNoArgs()->andReturn($mockedURL);
                $mock->shouldReceive('ip')->once()->withNoArgs()->andReturn(null);
                $mock->shouldReceive('method')->once()->withNoArgs()->andReturn($mockedMethod);
            }
        );
        assert($mockRequest instanceof Request);

        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedURL,
                $mockedMethod,
                $mockedRequestID,
                $processingStatus,
            ) {
                $mock->shouldReceive('requestID')->once()->with($mockedRequestID)->andReturn($mock);
                $mock->shouldReceive('ip')->once()->with('')->andReturn($mock);
                $mock->shouldReceive('endpoint')->once()->with("{$mockedMethod} {$mockedURL}")->andReturn($mock);
                $mock->shouldReceive('processingStatus')->once()->with($processingStatus)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService($mockRequest)->buildHTTP($mockLogBuilder, $processingStatus);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }
}
