<?php

namespace Tests\Unit\Exception\Http;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use App\Exceptions\Http\ConflictException;
use Exception;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ConflictExceptionTest extends TestCase
{
    protected string $mockedMessage;
    protected Collection $mockedJsonResponse;
    protected ExceptionMessage $mockExceptionMessage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedMessage = 'some error message';
        $this->mockedJsonResponse = collect([
            'message' => 'something',
        ]);

        $this->mockExceptionMessage = $this->mock(ExceptionMessage::class, function (
            MockInterface $mock
        ) {
            $mock->shouldReceive('getMessage')->andReturn($this->mockedMessage);
            $mock->shouldReceive('getJsonResponse')->andReturn($this->mockedJsonResponse);
        });
    }

    #[Test]
    public function getCode_should_return_right_json_content()
    {
        // Act
        $exception = new ConflictException($this->mockExceptionMessage);
        $result = $exception->getCode();


        // Assert
        $this->assertEquals(Response::HTTP_CONFLICT, $result);
    }

    #[Test]
    public function getMessage_should_return_right_json_content()
    {
        // Act
        $exception = new ConflictException($this->mockExceptionMessage);
        $result = $exception->getMessage();


        // Assert
        $this->assertEquals($this->mockedMessage, $result);
    }

    #[Test]
    #[DataProviderExternal(DataProvider::class, 'previousExceptions')]
    public function getPrevious_should_return_right_json_content(?Exception $mockPreviousException)
    {
        // Act
        $exception = new ConflictException(
            $this->mockExceptionMessage,
            $mockPreviousException
        );
        $result = $exception->getPrevious();


        // Assert
        $this->assertEquals($mockPreviousException, $result);
    }

    #[Test]
    public function render_should_return_right_json_content()
    {
        // Act
        $exception = new ConflictException($this->mockExceptionMessage);
        $result = $exception->render('');


        // Assert
        $this->assertEqualsCanonicalizing(
            $this->mockedJsonResponse->toArray(),
            json_decode($result->content(), true)
        );
    }
}
