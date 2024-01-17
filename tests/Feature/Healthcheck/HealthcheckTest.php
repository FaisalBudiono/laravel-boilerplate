<?php

declare(strict_types=1);

namespace Tests\Feature\Healthcheck;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\ValueObject\HealthcheckStatus;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Core\Logger\Message\ProcessingStatus;
use App\Core\Logger\Message\ValueObject\LogMessage;
use App\Exceptions\Http\InternalServerErrorException;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Core\Logger\Message\MockerLogMessageDirector;

class HealthcheckTest extends BaseFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(
            HealthcheckCoreContract::class,
            $this->mock(HealthcheckCoreContract::class),
        );
        $this->instance(
            LogMessageDirectorContract::class,
            $this->mock(LogMessageDirectorContract::class),
        );
        $this->instance(
            LogMessageBuilderContract::class,
            $this->mock(LogMessageBuilderContract::class),
        );
        Log::partialMock();
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown()
    {
        // Assert
        $this->withoutExceptionHandling();

        $mockException = new \Error($this->faker->sentence);
        $mockCore = $this->mock(
            HealthcheckCoreContract::class,
            function (MockInterface $mock) use ($mockException) {
                $mock->shouldReceive('getHealthiness')
                    ->once()
                    ->andThrow($mockException);
            }
        );
        $this->instance(HealthcheckCoreContract::class, $mockCore);

        $mockedLogMessage = $this->mock(LogMessage::class);

        $mockLogMessageBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use ($mockedLogMessage) {
                $mock->shouldReceive('message')->once()->with('Healthcheck endpoint')->andReturn($mock);

                $mock->shouldReceive('build')->twice()->andReturn($mockedLogMessage);
            }
        );
        assert($mockLogMessageBuilder instanceof LogMessageBuilderContract);
        $this->instance(LogMessageBuilderContract::class, $mockLogMessageBuilder);

        $this->instance(
            LogMessageDirectorContract::class,
            MockerLogMessageDirector::make($this, $mockLogMessageBuilder)
                ->http(ProcessingStatus::BEGIN)
                ->http(ProcessingStatus::ERROR)
                ->forException($mockException)
                ->build(),
        );

        Log::shouldReceive('info')
            ->with($mockedLogMessage)
            ->once();
        Log::shouldReceive('error')
            ->with($mockedLogMessage)
            ->once();


        try {
            // Act
            $this->getJson(
                $this->getEndpointUrl(),
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectException = new InternalServerErrorException(
                new ExceptionMessageGeneric(),
                $mockException,
            );
            $this->assertEquals($expectException, $e);
        }
    }

    #[Test]
    #[DataProvider('healthyHealthStatusDataProvider')]
    public function should_show_200_when_all_dependencies_is_healthy(
        HealthcheckResponse $mockedHealthcheckResponse,
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

        $mockedLogMessage = $this->mock(LogMessage::class);

        $mockLogMessageBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use ($mockedLogMessage) {
                $mock->shouldReceive('message')->twice()->with('Healthcheck endpoint')->andReturn($mock);

                $mock->shouldReceive('build')->twice()->andReturn($mockedLogMessage);
            }
        );
        assert($mockLogMessageBuilder instanceof LogMessageBuilderContract);
        $this->instance(LogMessageBuilderContract::class, $mockLogMessageBuilder);

        $this->instance(
            LogMessageDirectorContract::class,
            MockerLogMessageDirector::make($this, $mockLogMessageBuilder)
                ->http(ProcessingStatus::BEGIN)
                ->http(ProcessingStatus::SUCCESS)
                ->build(),
        );

        Log::shouldReceive('info')
            ->with($mockedLogMessage)
            ->twice();


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
        );


        // Assert
        $response->assertOk();
        $response->assertExactJson($mockedHealthcheckResponse->toArray());
    }

    public static function healthyHealthStatusDataProvider(): array
    {
        return [
            'when all dependencies is healthy (1 dependency)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', null),
                ),
            ],
            'when all dependencies is healthy (2 dependency)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', null),
                    new HealthcheckStatus('redis', null),
                ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('badHealthStatusDataProvider')]
    public function should_show_503_when_some_dependency_is_bad(
        HealthcheckResponse $mockedHealthcheckResponse,
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

        $mockedLogMessage = $this->mock(LogMessage::class);

        $mockLogMessageBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use ($mockedLogMessage, $mockedHealthcheckResponse) {
                $mock->shouldReceive('message')->twice()->with('Healthcheck endpoint')->andReturn($mock);

                $mock->shouldReceive('meta')->once()->with([
                    'detail' => $mockedHealthcheckResponse->toArrayDetail(),
                ])->andReturn($mock);

                $mock->shouldReceive('build')->twice()->andReturn($mockedLogMessage);
            }
        );
        assert($mockLogMessageBuilder instanceof LogMessageBuilderContract);
        $this->instance(LogMessageBuilderContract::class, $mockLogMessageBuilder);

        $this->instance(
            LogMessageDirectorContract::class,
            MockerLogMessageDirector::make($this, $mockLogMessageBuilder)
                ->http(ProcessingStatus::BEGIN)
                ->http(ProcessingStatus::SUCCESS)
                ->build(),
        );

        Log::shouldReceive('info')
            ->with($mockedLogMessage)
            ->once();
        Log::shouldReceive('emergency')
            ->with($mockedLogMessage)
            ->once();

        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
        );


        // Assert
        $response->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $response->assertExactJson($mockedHealthcheckResponse->toArray());
    }

    public static function badHealthStatusDataProvider(): array
    {
        return [
            'when one of dependencies is NOT healthy' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', new \Error('foo bar')),
                    new HealthcheckStatus('redis', null),
                ),
            ],
            'when all dependencies is NOT healthy' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', new \Error('foo bar')),
                    new HealthcheckStatus('redis', new \Error('foo bar')),
                ),
            ],
        ];
    }

    protected function getEndpointUrl(): string
    {
        return route('healthcheck');
    }
}
