<?php

namespace Tests\Unit\Http\Middleware;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Http\Middleware\XRequestIDMiddleware;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class XRequestIDMiddlewareTest extends TestCase
{
    #[Test]
    public function should_attach_x_request_id_in_request_and_response_header(): void
    {
        // Arrange
        $headerName = 'X-Request-Id';

        $mockedRandomString = $this->faker()->uuid;

        $mockRequest = new Request();


        // Assert
        $mockRandomizer = $this->mock(
            Randomizer::class,
            function (MockInterface $mock) use ($mockedRandomString) {
                $mock->shouldReceive('getRandomizeString')
                    ->once()
                    ->andReturn($mockedRandomString);
            }
        );
        assert($mockRandomizer instanceof Randomizer);


        // Act
        $middleware = new XRequestIDMiddleware($mockRandomizer);

        $response = $middleware->handle(
            $mockRequest,
            function (Request $argRequest) use ($mockedRandomString, $headerName) {
                $this->assertSame(
                    $mockedRandomString,
                    $argRequest->header($headerName)
                );
                return new Response();
            }
        );


        // Assert
        $this->assertSame(
            $mockedRandomString,
            $response->headers->get($headerName)
        );
    }
}
