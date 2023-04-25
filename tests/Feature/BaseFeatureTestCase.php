<?php

namespace Tests\Feature;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use Exception;
use Mockery\MockInterface;
use Tests\TestCase;

class BaseFeatureTestCase extends TestCase
{
    private string $mockedRequestId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockXRequestIdHeader();
    }

    protected function getMockedRequestId(): string
    {
        return $this->mockedRequestId;
    }

    protected function mockXRequestIdHeader(): void
    {
        $this->mockedRequestId = $this->faker->uuid;

        $this->instance(Randomizer::class, $this->mock(
            Randomizer::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('getRandomizeString')
                    ->andReturn($this->mockedRequestId);
            }
        ));
    }

    protected function validateLoggingBegin(
        string $argEndpoint,
        string $argRequestID,
        ProcessingStatus $argProcessingStatus,
        string $argMessage,
        array $argMeta,
        string $endpoint,
        string $message,
        array $input,
    ): bool {
        try {
            $this->assertSame($endpoint, $argEndpoint);
            $this->assertSame($this->getMockedRequestId(), $argRequestID);
            $this->assertSame(ProcessingStatus::BEGIN, $argProcessingStatus);
            $this->assertSame($message, $argMessage);
            $this->assertSame([
                'input' => $input,
            ], $argMeta);
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected function validateLoggingError(
        string $argEndpoint,
        string $argRequestID,
        ProcessingStatus $argProcessingStatus,
        string $argMessage,
        array $argMeta,
        string $endpoint,
        Exception $e,
    ): bool {
        try {
            $this->assertSame($endpoint, $argEndpoint);
            $this->assertSame($this->getMockedRequestId(), $argRequestID);
            $this->assertSame(ProcessingStatus::ERROR, $argProcessingStatus);
            $this->assertSame($e->getMessage(), $argMessage);
            $this->assertSame([
                'trace' => $e->getTrace(),
            ], $argMeta);
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected function validateLoggingSuccess(
        string $argEndpoint,
        string $argRequestID,
        ProcessingStatus $argProcessingStatus,
        string $argMessage,
        array $argMeta,
        string $endpoint,
        string $message,
    ): bool {
        try {
            $this->assertSame($endpoint, $argEndpoint);
            $this->assertSame($this->getMockedRequestId(), $argRequestID);
            $this->assertSame(ProcessingStatus::SUCCESS, $argProcessingStatus);
            $this->assertSame($message, $argMessage);
            $this->assertSame([], $argMeta);
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }
}
