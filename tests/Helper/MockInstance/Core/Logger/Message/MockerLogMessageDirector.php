<?php

declare(strict_types=1);

namespace Tests\Helper\MockInstance\Core\Logger\Message;

use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Core\Logger\Message\ProcessingStatus;
use Mockery\MockInterface;
use Tests\TestCase;

class MockerLogMessageDirector extends TestCase
{
    protected MockInterface|LogMessageDirectorContract $builder;

    protected function __construct(
        TestCase $testCase,
        protected LogMessageBuilderContract $logBuilder,
    ) {
        $this->builder = $testCase->mock(LogMessageDirectorContract::class);
    }

    public function forException(
        \Throwable $expectedException,
    ): self {
        $this->builder->shouldReceive('buildForException')
            ->once()
            ->withArgs(function ($argBuilder, $argException) use ($expectedException) {
                try {
                    $this->assertEquals($this->logBuilder, $argBuilder);
                    $this->assertEquals($expectedException, $argException);
                    return true;
                } catch (\Throwable $e) {
                    dd($e);
                }
            })
            ->andReturn($this->logBuilder);
        return $this;
    }

    public function http(ProcessingStatus $processingStatus): self
    {
        $this->builder->shouldReceive('buildHTTP')
            ->withArgs(function ($arg, $argProcessingStatus) use ($processingStatus) {
                try {
                    $this->assertEquals($this->logBuilder, $arg);
                    $this->assertEquals($processingStatus, $argProcessingStatus);
                    return true;
                } catch (\Throwable $e) {
                    return false;
                }
            })->andReturn($this->logBuilder);

        return $this;
    }

    public function build(): LogMessageDirectorContract
    {
        return $this->builder;
    }

    public static function make(
        TestCase $testCase,
        LogMessageBuilderContract $logMessageBuilder,
    ): self {
        return new self($testCase, $logMessageBuilder);
    }
}
