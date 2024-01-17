<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Logger\Message\LogMessageDirector;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Exceptions\Core\BusinessLogicException;
use App\Exceptions\Http\UnprocessableEntityException;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class LogMessageDirector_BuildForException_Test extends LogMessageDirectorBaseTestCase
{
    #[Test]
    #[DataProvider('exceptionDataProvider')]
    public function should_return_builder_correctly(
        \Throwable $mockedException,
        array $expectedMeta,
    ): void {
        // Arrange
        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use ($mockedException, $expectedMeta) {
                $mock->shouldReceive('message')->once()->with($mockedException->getMessage())->andReturn($mock);
                $mock->shouldReceive('meta')->once()->with($expectedMeta)->andReturn($mock);
            }
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);


        // Act
        $result = $this->makeService()->buildForException($mockLogBuilder, $mockedException);


        // Assert
        $this->assertEquals($mockLogBuilder, $result);
    }

    public static function exceptionDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'generic exception' => [
                $mockedException = new \Exception($faker->sentence()),
                [
                    'detail' => null,
                    'file' => $mockedException->getFile(),
                    'line' => $mockedException->getLine(),
                    'trace' => $mockedException->getTrace(),
                ]
            ],
            'HTTP exception class' => [
                $mockedException = new UnprocessableEntityException(new ExceptionMessageStandard(
                    $faker->sentence(),
                    $faker->sentence(),
                )),
                [
                    'detail' => null,
                    'file' => $mockedException->getFile(),
                    'line' => $mockedException->getLine(),
                    'trace' => $mockedException->getTrace(),
                ]
            ],
            'base exception class' => [
                $mockedException = new BusinessLogicException(new ExceptionMessageStandard(
                    $faker->sentence(),
                    $faker->sentence(),
                )),
                [
                    'detail' => $mockedException->exceptionMessage->getJsonResponse()->toArray(),
                    'file' => $mockedException->getFile(),
                    'line' => $mockedException->getLine(),
                    'trace' => $mockedException->getTrace(),
                ]
            ],
        ];
    }
}
