<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatter;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use App\Core\Query\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\Helper\ResourceAssertion\User\ResourceAssertionUserList;

class GetAllUserTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected ResourceAssertion $resourceAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->count(10)->create();

        $this->resourceAssertion = new ResourceAssertionUserList;

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
        $this->instance(
            LoggerMessageFormatterFactoryContract::class,
            $this->mock(LoggerMessageFormatterFactoryContract::class)
        );
        Log::partialMock();
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input
    ) {
        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
        );


        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor($errorMaker, 'errors.meta');
    }

    public static function invalidDataProvider(): array
    {
        return [
            'orderBy is not string (now contain array)' => [
                'orderBy',
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => ['kuda'],
                    ])->toArray(),
            ],
            'orderBy is not string (now contain integer)' => [
                'orderBy',
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => 123,
                    ])->toArray(),
            ],
            'orderBy is not valid enum (now contain random string)' => [
                'orderBy',
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => 'kuda',
                    ])->toArray(),
            ],

            'orderDir is not string (now contain array)' => [
                'orderDir',
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => ['kuda'],
                    ])->toArray(),
            ],
            'orderDir is not string (now contain integer)' => [
                'orderDir',
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => 123,
                    ])->toArray(),
            ],
            'orderDir is not valid enum (now contain random string)' => [
                'orderDir',
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => 'kuda',
                    ])->toArray(),
            ],

            'page is not integer (now contain array)' => [
                'page',
                collect(self::validRequestInput())
                    ->replace([
                        'page' => [2],
                    ])->toArray(),
            ],
            'page is not integer (now contain string)' => [
                'page',
                collect(self::validRequestInput())
                    ->replace([
                        'page' => 'kambing',
                    ])->toArray(),
            ],

            'perPage is not integer (now contain array)' => [
                'perPage',
                collect(self::validRequestInput())
                    ->replace([
                        'perPage' => [2],
                    ])->toArray(),
            ],
            'perPage is not integer (now contain string)' => [
                'perPage',
                collect(self::validRequestInput())
                    ->replace([
                        'perPage' => 'kambing',
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown()
    {
        // Arrange
        $input = $this->validRequestInput();
        $exceptionMessage = new ExceptionMessageGeneric;

        $mockException = new Exception('generic error');

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn (
                        GetAllUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
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
                $input,
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
                        'Get all user endpoint',
                        collect($input)
                            ->map(fn ($val) => (string) $val)
                            ->toArray(),
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
            $this->getEndpointUrl() . '?' . http_build_query($input),
            $input
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonPath(
            'errors',
            $exceptionMessage->getJsonResponse()->toArray()
        );
    }

    #[Test]
    #[DataProvider('validDataProvider')]
    public function should_show_200_when_successfully_get_user_list(
        array $input,
        array $loggedInput,
    ) {
        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn (
                        GetAllUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andReturn(User::query()->paginate());
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        $logInfoValue = $this->faker->sentence;
        $logSuccessValue = $this->faker->sentence;

        $mockLoggerFormatterFactory = $this->mock(
            LoggerMessageFormatterFactoryContract::class,
            function (MockInterface $mock) use (
                $logInfoValue,
                $logSuccessValue,
                $loggedInput,
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
                        'Get all user endpoint',
                        $loggedInput,
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
                        'Get all user endpoint',
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
            $this->getEndpointUrl() . '?' . http_build_query($input),
            $input
        );


        // Assert
        $response->assertOk();
        $this->resourceAssertion->assertResource($this, $response);
    }

    public static function validDataProvider(): array
    {
        return [
            'complete data' => [
                collect(self::validRequestInput())
                    ->toArray(),
                collect(self::validRequestInput())
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],

            'orderBy is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => null
                    ])->toArray(),
                collect(self::validRequestInput())
                    ->except('orderBy')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],
            'without orderBy' => [
                collect(self::validRequestInput())
                    ->except('orderBy')
                    ->toArray(),
                collect(self::validRequestInput())
                    ->except('orderBy')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],

            'orderDir is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => null
                    ])->toArray(),
                collect(self::validRequestInput())
                    ->except('orderDir')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],
            'without orderDir' => [
                collect(self::validRequestInput())
                    ->except('orderDir')
                    ->toArray(),
                collect(self::validRequestInput())
                    ->except('orderDir')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],

            'page is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'page' => null
                    ])->toArray(),
                collect(self::validRequestInput())
                    ->except('page')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],
            'without page' => [
                collect(self::validRequestInput())
                    ->except('page')
                    ->toArray(),
                collect(self::validRequestInput())
                    ->except('page')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],

            'perPage is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'perPage' => null
                    ])->toArray(),
                collect(self::validRequestInput())
                    ->except('perPage')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],
            'without perPage' => [
                collect(self::validRequestInput())
                    ->except('perPage')
                    ->toArray(),
                collect(self::validRequestInput())
                    ->except('perPage')
                    ->map(fn ($val) => (string) $val)
                    ->toArray(),
            ],
        ];
    }

    protected function getEndpointInfo(): string
    {
        return "GET {$this->getEndpointUrl()}";
    }

    protected function getEndpointUrl(): string
    {
        return route('user.index');
    }

    protected function validateRequest(
        GetAllUserPort $argInput,
        array $input
    ): bool {
        try {
            $this->assertSame(
                $input['orderBy'] ?? null,
                $argInput->getOrderBy()?->value
            );
            $this->assertSame(
                $input['orderDir'] ?? null,
                $argInput->getOrderDirection()?->value
            );
            $this->assertSame(
                $input['page'] ?? null,
                $argInput->getPage()
            );
            $this->assertSame(
                $input['perPage'] ?? null,
                $argInput->getPerPage()
            );
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected static function validRequestInput(): array
    {
        return [
            'orderBy' => UserOrderBy::NAME->value,
            'orderDir' => OrderDirection::ASCENDING->value,
            'page' => 1,
            'perPage' => 15,
        ];
    }
}
