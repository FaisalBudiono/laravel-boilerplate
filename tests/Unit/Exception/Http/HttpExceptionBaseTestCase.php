<?php

namespace Tests\Unit\Exception\Http;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use Exception;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

abstract class HttpExceptionBaseTestCase extends TestCase
{
    protected Collection $mockedJsonResponse;
    protected ExceptionMessage $mockExceptionMessage;

    abstract protected function getHttpStatusCode(): int;
    abstract protected function makeHttpException(): string;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedJsonResponse = collect([
            'message' => 'something',
        ]);

        $this->mockExceptionMessage = $this->mock(ExceptionMessage::class, function (
            MockInterface $mock
        ) {
            $mock->shouldReceive('getJsonResponse')->andReturn($this->mockedJsonResponse);
        });
    }

    #[Test]
    public function getCode_should_return_right_json_content(): void
    {
        // Act
        $exception = new ($this->makeHttpException())($this->mockExceptionMessage);
        $result = $exception->getCode();


        // Assert
        $this->assertEquals($this->getHttpStatusCode(), $result);
    }

    #[Test]
    public function getMessage_should_return_right_message(): void
    {
        // Act
        $exception = new ($this->makeHttpException())($this->mockExceptionMessage);
        $result = $exception->getMessage();


        // Assert
        $this->assertEquals($this->mockedJsonResponse->toJson(), $result);
    }

    #[Test]
    #[DataProviderExternal(DataProvider::class, 'previousExceptions')]
    public function getPrevious_should_return_right_json_content(?Exception $mockPreviousException): void
    {
        // Act
        $exception = new ($this->makeHttpException())(
            $this->mockExceptionMessage,
            $mockPreviousException
        );
        $result = $exception->getPrevious();


        // Assert
        $this->assertEquals($mockPreviousException, $result);
    }

    #[Test]
    public function render_should_return_right_json_content(): void
    {
        // Act
        $exception = new ($this->makeHttpException())($this->mockExceptionMessage);
        $result = $exception->render('');


        // Assert
        $this->assertEqualsCanonicalizing(
            [
                'errors' => $this->mockedJsonResponse->toArray(),
            ],
            json_decode($result->content(), true)
        );
    }
}
