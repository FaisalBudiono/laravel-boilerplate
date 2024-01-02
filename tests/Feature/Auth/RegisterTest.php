<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Port\Core\User\CreateUserPort;
use Exception;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\Trait\JSONTrait;

class RegisterTest extends BaseFeatureTestCase
{
    use JSONTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input
    ): void {
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
    public function should_show_409_when_thrown_duplicated_email(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $input = $this->validRequestInput();


        // Assert
        $mockException = new UserEmailDuplicatedException(new ExceptionMessageStandard(
            $this->faker->sentence(),
            $this->faker->word(),
        ));

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


        try {
            // Act
            $this->postJson(
                $this->getEndpointUrl(),
                $input
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new ConflictException(
                $mockException->exceptionMessage,
                $mockException,
            );
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $input = $this->validRequestInput();

        $mockException = new Exception($this->faker->sentence());


        // Assert
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


        try {
            // Act
            $this->postJson(
                $this->getEndpointUrl(),
                $input
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InternalServerErrorException(
                new ExceptionMessageGeneric(),
                $mockException,
            );
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    public function should_show_201_when_successfully_create_user(): void
    {
        // Arrange
        $input = $this->validRequestInput();
        $mockedUser = User::factory()->create()->fresh();


        // Assert
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
            $this->getEndpointUrl(),
            $input
        );


        // Assert
        $response->assertCreated(Response::HTTP_CREATED);
        $response->assertJsonPath(
            'data',
            $this->jsonToArray(UserResource::make($mockedUser)->toJson()),
        );
    }

    protected function getEndpointUrl(): string
    {
        return route('register');
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
