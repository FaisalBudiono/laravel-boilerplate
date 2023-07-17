<?php

namespace Tests\Helper\MockInstance;

use Exception;
use Tests\Helper\MockInstance\LoggerMessageFactory\Implementor;
use Tests\TestCase;

class MockerLoggerMessageFactory
{
    protected Implementor $implementor;

    public function __construct(
        TestCase $test,
    ) {
        $this->implementor = Implementor::make($test);
    }

    public static function make(TestCase $test): self
    {
        return new self($test);
    }

    public function bindInstance(): void
    {
        $this->implementor->bindInstance();
    }

    public function setHTTPError(Exception $e, string $logMessage): self
    {
        $this->implementor->setHTTPError($e, $logMessage);

        return $this;
    }

    public function setHTTPStart(
        string $message,
        array $input,
        string $logMessage,
    ): self {
        $this->implementor->setHTTPStart($message, $input, $logMessage);

        return $this;
    }

    public function setHTTPSuccess(string $message, string $logMessage): self
    {
        $this->implementor->setHTTPSuccess($message, $logMessage);

        return $this;
    }
}
