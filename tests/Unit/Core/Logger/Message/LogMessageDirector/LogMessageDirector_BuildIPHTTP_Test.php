<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Logger\Message\LogMessageBuilderContract;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class LogMessageDirector_BuildIPHTTP_Test extends LogMessageDirectorBaseTestCase
{
    #[Test]
    public function should_return_builder_correctly(): void
    {
        // Arrange
        $mockedIP = $this->faker->ipv4();

        $mockRequest = $this->mock(
            Request::class,
            function (MockInterface $mock) use (
                $mockedIP,
            ) {
                $mock->shouldReceive('ip')->once()->withNoArgs()->andReturn($mockedIP);
            }
        );
        assert($mockRequest instanceof Request);

        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedIP,
            ) {
                $mock->shouldReceive('ip')->once()->with($mockedIP)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService($mockRequest)->buildIPHTTP($mockLogBuilder);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }
}
