<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Http\Middleware\LoggingMiddleware;
use Mockery\MockInterface;
use Tests\TestCase;

class BaseFeatureTestCase extends TestCase
{
    protected string $mockedRequestId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockXRequestIdHeader();
        $this->mockLoggingMiddleware();
    }

    protected function getMockedRequestId(): string
    {
        return $this->mockedRequestId;
    }

    protected function mockLoggingMiddleware(): void
    {
        $this->mock(LoggingMiddleware::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')
                ->andReturnUsing(function ($argRequest, $argNext) {
                    return $argNext($argRequest);
                });
        });
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
}
