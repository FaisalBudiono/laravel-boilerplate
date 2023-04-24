<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessage;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\Helper\ResourceAssertion\User\ResourceAssertionUser;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    protected ResourceAssertion $resourceAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceAssertion = new ResourceAssertionUser;

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input
    ) {
        // Act
        $response = $this->postJson(
            route('user.store'),
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

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockExceptionMessage, $input) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(fn (
                        CreateUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow(
                        new UserEmailDuplicatedException($mockExceptionMessage)
                    );
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            route('user.store'),
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

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(fn (
                        CreateUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow(new \Exception('generic error'));
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            route('user.store'),
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


        // Act
        $response = $this->postJson(
            route('user.store'),
            $input
        );


        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $this->resourceAssertion->assertResource($this, $response);
    }

    protected static function validRequestInput(): array
    {
        return [
            'email' => 'faisal@budiono.com',
            'name' => 'faisal budiono',
            'password' => 'password',
        ];
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
