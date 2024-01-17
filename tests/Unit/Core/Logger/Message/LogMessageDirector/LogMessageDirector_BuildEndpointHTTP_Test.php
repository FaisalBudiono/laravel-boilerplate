<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\LogMessageBuilderContract;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class LogMessageDirector_BuildEndpointHTTP_Test extends LogMessageDirectorBaseTestCase
{
    #[Test]
    public function should_return_builder_correctly(): void
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
}
