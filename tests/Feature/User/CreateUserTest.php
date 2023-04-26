<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatter;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\Helper\ResourceAssertion\User\ResourceAssertionUser;

class CreateUserTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected ResourceAssertion $resourceAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceAssertion = new ResourceAssertionUser;

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
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input
        );


        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor($errorMaker, 'errors.meta');
    }

    public static function invalidDataProvider(): array
    {
        return [
            'without email' => [
                'email',
                collect(self::validRequestInput())
                    ->except('email')
                    ->toArray(),
            ],
            'email is null' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => null,
                    ])->toArray(),
            ],
            'email is empty string' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => '',
                    ])->toArray(),
            ],
            'email is not in right format (now contain random string)' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => fake()->words(3, true),
                    ])->toArray(),
            ],
            'email is not in right format (now contain array)' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => [fake()->words(3, true)],
                    ])->toArray(),
            ],
            'email should be less than 250 (currently 251)' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => fake()->regexify('[a-z]{241}@gmail.com'),
                    ])->toArray(),
            ],

            'without name' => [
                'name',
                collect(self::validRequestInput())
                    ->except('name')
                    ->toArray(),
            ],
            'name is null' => [
                'name',
                collect(self::validRequestInput())
                    ->replace([
                        'name' => null,
                    ])->toArray(),
            ],
            'name is empty string' => [
                'name',
                collect(self::validRequestInput())
                    ->replace([
                        'name' => null,
                    ])->toArray(),
            ],
            'name is more than 250 (currently 251)' => [
                'name',
                collect(self::validRequestInput())
                    ->replace([
                        'name' => fake()->regexify('[a-z]{251}'),
                    ])->toArray(),
            ],

            'without password' => [
                'password',
                collect(self::validRequestInput())
                    ->except('password')
                    ->toArray(),
            ],
            'password is null' => [
                'password',
                collect(self::validRequestInput())
                    ->replace([
                        'password' => null,
                    ])->toArray(),
            ],
            'password is empty string' => [
                'password',
                collect(self::validRequestInput())
                    ->replace([
                        'password' => null,
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_409_when_thrown_duplicated_email()
    {
        // Assert
        $input = $this->validRequestInput();

        $mockedExceptionResponse = collect(['foo' => 'bar']);
        /** @var ExceptionMessage */
        $mockExceptionMessage = $this->mock(
            ExceptionMessage::class,
            function (MockInterface $mock) use ($mockedExceptionResponse) {
                $mock->shouldReceive('getJsonResponse')
                    ->andReturn($mockedExceptionResponse);
                $mock->shouldReceive('getMessage');
            }
        );
        $mockException = new UserEmailDuplicatedException($mockExceptionMessage);

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException, $input) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(fn (
                        CreateUserPort $argInput
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
                        'Create user endpoint',
                        $input,
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
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input
        );


        // Assert
        $response->assertStatus(Response::HTTP_CONFLICT);
        $response->assertJsonPath('errors', $mockedExceptionResponse->toArray());
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown()
    {
        // Assert
        $input = $this->validRequestInput();
        $exceptionMessage = new ExceptionMessageGeneric;

        $mockException = new Exception('generic error');

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(fn (
                        CreateUserPort $argInput
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
                        'Create user endpoint',
                        $input,
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
        $response = $this->postJson(
            $this->getEndpointUrl(),
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
    public function should_show_201_when_successfully_create_user()
    {
        // Assert
        $input = $this->validRequestInput();
        /** @var User */
        $mockedUser = User::factory()->create();

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedUser) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(fn (
                        CreateUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andReturn($mockedUser);
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
                        'Create user endpoint',
                        $input,
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
                        'Create user endpoint',
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
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input
        );


        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $this->resourceAssertion->assertResource($this, $response);
    }

    protected function getEndpointInfo(): string
    {
        return "POST {$this->getEndpointUrl()}";
    }

    protected function getEndpointUrl(): string
    {
        return route('user.store');
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

    protected static function validRequestInput(): array
    {
        return [
            'email' => 'faisal@budiono.com',
            'name' => 'faisal budiono',
            'password' => 'password',
        ];
    }
}
