<?php

namespace Tests\Feature;

use App\Core\Formatter\Randomizer\Randomizer;
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
}
