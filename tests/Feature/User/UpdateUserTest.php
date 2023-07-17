<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\User;
use App\Port\Core\User\UpdateUserPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\MockerLoggerMessageFactory;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\Helper\ResourceAssertion\User\ResourceAssertionUser;

class UpdateUserTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ResourceAssertion $resourceAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceAssertion = new ResourceAssertionUser;

        $this->user = User::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ]);

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
        $this->instance(
            LoggerMessageFactoryContract::class,
            $this->mock(LoggerMessageFactoryContract::class),
        );
        Log::partialMock();
    }

    #[Test]
    public function should_show_404_when_user_id_is_not_found()
    {
        // Arrange
        $notFoundId = $this->user->id + 1;


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($notFoundId),
        );


        // Assert
        $response->assertNotFound();
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input,
    ) {
        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
            $input,
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
    public function should_show_409_when_thrown_duplicated_email_exception()
    {
        // Arrange
        $input = $this->validRequestInput();

        $logInfoMessage = $this->faker->sentence;
        $logErrorMessage = $this->faker->sentence;


        // Assert
        $mockedExceptionResponse = collect(['foo' => 'bar']);
        $mockExceptionMessage = $this->mock(
            ExceptionMessage::class,
            function (MockInterface $mock) use ($mockedExceptionResponse) {
                $mock->shouldReceive('getJsonResponse')
                    ->atLeast()
                    ->once()
                    ->andReturn($mockedExceptionResponse);
            }
        );
        assert($mockExceptionMessage instanceof ExceptionMessage);

        $mockException = new UserEmailDuplicatedException($mockExceptionMessage);

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException, $input) {
                $mock->shouldReceive('update')
                    ->once()
                    ->withArgs(fn (
                        UpdateUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Update user endpoint',
                $input,
                $logInfoMessage,
            )->setHTTPError(
                $mockException,
                $logErrorMessage,
            )->bindInstance();

        Log::shouldReceive('info')
            ->withArgs(function ($argLogMessage) use ($logInfoMessage) {
                try {
                    $this->assertEquals($logInfoMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();
        Log::shouldReceive('warning')
            ->withArgs(function ($argLogMessage) use ($logErrorMessage) {
                try {
                    $this->assertEquals($logErrorMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
            $input,
        );


        // Assert
        $response->assertStatus(Response::HTTP_CONFLICT);
        $response->assertJsonPath('errors', $mockedExceptionResponse->toArray());
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown()
    {
        // Arrange
        $input = $this->validRequestInput();

        $exceptionMessage = new ExceptionMessageGeneric;
        $mockException = new Exception($this->faker->sentence);

        $logInfoMessage = $this->faker->sentence;
        $logErrorMessage = $this->faker->sentence;


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('update')
                    ->once()
                    ->withArgs(fn (
                        UpdateUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Update user endpoint',
                $input,
                $logInfoMessage,
            )->setHTTPError(
                $mockException,
                $logErrorMessage,
            )->bindInstance();

        Log::shouldReceive('info')
            ->withArgs(function ($argLogMessage) use ($logInfoMessage) {
                try {
                    $this->assertEquals($logInfoMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();
        Log::shouldReceive('error')
            ->withArgs(function ($argLogMessage) use ($logErrorMessage) {
                try {
                    $this->assertEquals($logErrorMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();

        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
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
    public function should_show_200_when_successfully_update_user()
    {
        // Arrange
        $input = $this->validRequestInput();
        $mockedUser = User::factory()->create();

        $logInfoMessage = $this->faker->sentence;
        $logSuccessMessage = $this->faker->sentence;


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedUser) {
                $mock->shouldReceive('update')
                    ->once()
                    ->withArgs(fn (
                        UpdateUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andReturn($mockedUser);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Update user endpoint',
                $input,
                $logInfoMessage,
            )->setHTTPSuccess(
                'Update user endpoint',
                [],
                $logSuccessMessage,
            )->bindInstance();

        Log::shouldReceive('info')
            ->withArgs(function ($argLogMessage) use ($logInfoMessage) {
                try {
                    $this->assertEquals($logInfoMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();
        Log::shouldReceive('info')
            ->withArgs(function ($argLogMessage) use ($logSuccessMessage) {
                try {
                    $this->assertEquals($logSuccessMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
            $input,
        );


        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $this->resourceAssertion->assertResource($this, $response);
    }

    protected function getEndpointUrl(int $userId): string
    {
        return route('user.update', ['userID' => $userId]);
    }

    protected function validateRequest(UpdateUserPort $argInput, array $input): bool
    {
        try {
            $this->assertSame($input['email'], $argInput->getEmail());
            $this->assertSame($input['name'], $argInput->getName());
            $this->assertSame($input['password'], $argInput->getUserPassword());
            $this->assertTrue($argInput->getUserModel()->is($this->user));
            return true;
        } catch (Exception $e) {
            dd($e);
        }
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
