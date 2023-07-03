<?php

namespace Tests\Feature\Healthcheck;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\ValueObject\HealthcheckStatus;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatter;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use App\Core\User\UserCoreContract;
use App\Port\Core\User\CreateUserPort;
use Exception;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;

class HealthcheckTest extends BaseFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(HealthcheckCoreContract::class, $this->mock(HealthcheckCoreContract::class));
        $this->instance(
            LoggerMessageFormatterFactoryContract::class,
            $this->mock(LoggerMessageFormatterFactoryContract::class)
        );
        Log::partialMock();
    }


    #[Test]
    public function should_show_500_when_generic_error_is_thrown()
    {
        // Assert
        $mockException = new Exception('generic error');
        $mockCore = $this->mock(
            HealthcheckCoreContract::class,
            function (MockInterface $mock)  use ($mockException) {
                $mock->shouldReceive('getHealthiness')
                    ->once()
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        $logInfoValue = $this->faker->sentence;
        $logErrorValue = $this->faker->sentence;

        $mockLoggerFormatterFactory = $this->mock(
            LoggerMessageFormatterFactoryContract::class,
            function (MockInterface $mock) use (
                $logInfoValue,
                $logErrorValue,
                $mockException,
            ) {
                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingBegin(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo(),
                        'Healthcheck endpoint',
                        [],
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logInfoValue)
                        )
                    );

                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingError(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo(),
                        $mockException,
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logErrorValue)
                        )
                    );
            }
        );
        $this->instance(
            LoggerMessageFormatterFactoryContract::class,
            $mockLoggerFormatterFactory
        );

        Log::shouldReceive('info')
            ->with($logInfoValue)
            ->once();
        Log::shouldReceive('error')
            ->with($logErrorValue)
            ->once();


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        $exceptionMessage = new ExceptionMessageGeneric;

        $response->assertJsonPath(
            'errors',
            $exceptionMessage->getJsonResponse()->toArray()
        );
    }

    #[Test]
    #[DataProvider('healthStatusDataProvider')]
    public function should_show_health_status_with_correct_http_status(
        HealthcheckResponse $mockedHealthcheckResponse,
        int $expectedHTTPStatus,
    ) {
        // Assert
        $mockCore = $this->mock(
            HealthcheckCoreContract::class,
            function (MockInterface $mock) use ($mockedHealthcheckResponse) {
                $mock->shouldReceive('getHealthiness')
                    ->once()
                    ->andReturn($mockedHealthcheckResponse);
            }
        );
        $this->instance(HealthcheckCoreContract::class, $mockCore);

        $logInfoValue = $this->faker->sentence;
        $logSuccessValue = $this->faker->sentence;

        $mockLoggerFormatterFactory = $this->mock(
            LoggerMessageFormatterFactoryContract::class,
            function (MockInterface $mock) use (
                $logInfoValue,
                $logSuccessValue,
            ) {
                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingBegin(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo(),
                        'Healthcheck endpoint',
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logInfoValue)
                        )
                    );

                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingSuccess(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo(),
                        'Healthcheck endpoint',
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logSuccessValue)
                        )
                    );
            }
        );
        $this->instance(
            LoggerMessageFormatterFactoryContract::class,
            $mockLoggerFormatterFactory
        );

        Log::shouldReceive('info')
            ->with($logInfoValue)
            ->once();
        Log::shouldReceive('info')
            ->with($logSuccessValue)
            ->once();


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
        );


        // Assert
        $response->assertStatus($expectedHTTPStatus);
        $response->assertExactJson($mockedHealthcheckResponse->toArray());
    }

    public static function healthStatusDataProvider(): array
    {
        return [
            'when all dependencies is healthy (1 dependency)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', null),
                ),
                Response::HTTP_OK,
            ],
            'when all dependencies is healthy (2 dependency)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', null),
                    new HealthcheckStatus('redis', null),
                ),
                Response::HTTP_OK,
            ],

            'when one of dependencies is NOT healthy' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', new Exception('foo bar')),
                    new HealthcheckStatus('redis', null),
                ),
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
            'when all dependencies is NOT healthy' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', new Exception('foo bar')),
                    new HealthcheckStatus('redis', new Exception('foo bar')),
                ),
                Response::HTTP_INTERNAL_SERVER_ERROR,
            ],
        ];
    }

    protected function getEndpointInfo(): string
    {
        return "GET {$this->getEndpointUrl()}";
    }

    protected function getEndpointUrl(): string
    {
        return route('healthcheck');
    }

    protected function validateRequest(
        CreateUserPort $argInput,
        array $input
    ): bool {
        $this->assertSame($input['email'], $argInput->getEmail());
        $this->assertSame($input['name'], $argInput->getName());
        $this->assertSame($input['password'], $argInput->getUserPassword());
        return true;
    }
}
