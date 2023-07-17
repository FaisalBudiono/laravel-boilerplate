<?php

namespace Tests\Helper\MockInstance\LoggerMessageFactory;

use App\Core\Logger\Message\LoggerMessageFactoryContract;
use Exception;
use Mockery\MockInterface;
use Stringable;
use Tests\TestCase;

class Implementor extends TestCase
{
    protected LoggerMessageFactoryContract $loggerMessageFactory;
    protected MockInterface $mockInterface;

    public function __construct(
        protected TestCase $test,
    ) {
        $this->loggerMessageFactory = $this->test->mock(
            LoggerMessageFactoryContract::class,
            function (MockInterface $mock) {
                $this->mockInterface = $mock;
            }
        );
    }

    public static function make(TestCase $test): self
    {
        return new self($test);
    }

    public function bindInstance(): void
    {
        $this->test->instance(
            LoggerMessageFactoryContract::class,
            $this->loggerMessageFactory
        );
    }

    public function setHTTPError(Exception $e, string $logMessage): self
    {
        $this->mockInterface->shouldReceive('makeHTTPError')
            ->once()
            ->withArgs(function (
                Exception $argError,
            ) use ($e) {
                try {
                    $this->assertSame($e, $argError);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->andReturn($this->makeStringable($logMessage));

        return $this;
    }

    public function setHTTPStart(
        string $message,
        array $input,
        string $logMessage,
    ): self {
        $this->mockInterface->shouldReceive('makeHTTPStart')
            ->once()
            ->withArgs(function (
                string $argMessage,
                array $argInput
            ) use ($message, $input) {
                try {
                    $this->assertSame($message, $argMessage);
                    $this->assertEquals($input, $argInput);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->andReturn($this->makeStringable($logMessage));

        return $this;
    }

    public function setHTTPSuccess(
        string $message,
        array $meta,
        string $logMessage,
    ): self {
        $this->mockInterface->shouldReceive('makeHTTPSuccess')
            ->once()
            ->withArgs(function (
                string $argMessage,
                array $argMeta,
            ) use ($message, $meta) {
                try {
                    $this->assertSame($message, $argMessage);
                    $this->assertSame($meta, $argMeta);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->andReturn($this->makeStringable($logMessage));

        return $this;
    }

    protected function makeStringable(string $logMessage): Stringable
    {
        $stringable = $this->test->mock(
            Stringable::class,
            fn (MockInterface $mock) =>
            $mock->shouldReceive('__toString')
                ->once()
                ->withNoArgs()
                ->andReturn($logMessage)
        );
        assert($stringable instanceof Stringable);

        return $stringable;
    }
}
