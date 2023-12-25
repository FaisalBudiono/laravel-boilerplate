<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Core\Auth\AuthJWTCoreContract;
use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\InvalidCredentialException;
use App\Port\Core\Auth\LoginPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\BaseFeatureTestCase;

class LoginTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instance(
            AuthJWTCoreContract::class,
            $this->mock(AuthJWTCoreContract::class),
        );
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
                        'password' => '',
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown(): void
    {
        // Arrange
        $input = $this->validRequestInput();

        $exceptionMessage = new ExceptionMessageGeneric();
        $mockException = new \Error($this->faker->sentence());


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('login')
                    ->once()
                    ->withArgs(fn (
                        LoginPort $argInput,
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockException);
            }
        );
        $this->instance(AuthJWTCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input,
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonPath(
            'errors',
            $exceptionMessage->getJsonResponse()->toArray()
        );
    }

    #[Test]
    public function should_show_401_when_thrown_invalid_credential(): void
    {
        // Arrange
        $input = $this->validRequestInput();

        $mockException = new InvalidCredentialException(new ExceptionMessageStandard(
            $this->faker->sentence,
            $this->faker->sentence,
        ));


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('login')
                    ->once()
                    ->withArgs(fn (
                        LoginPort $argInput,
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockException);
            }
        );
        $this->instance(AuthJWTCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input,
        );


        // Assert
        $response->assertUnauthorized();
        $response->assertJsonPath(
            'errors',
            $mockException->exceptionMessage->getJsonResponse()->toArray(),
        );
    }

    #[Test]
    public function should_show_200_when_successfully_authenticated(): void
    {
        // Arrange
        $input = $this->validRequestInput();

        $mockedTokenPair = new TokenPair(
            $this->faker->sentence,
            $this->faker->sentence,
        );


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedTokenPair) {
                $mock->shouldReceive('login')
                    ->once()
                    ->withArgs(fn (
                        LoginPort $argInput,
                    ) => $this->validateRequest($argInput, $input))
                    ->andReturn($mockedTokenPair);
            }
        );
        $this->instance(AuthJWTCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input,
        );


        // Assert
        $response->assertOk();
        $response->assertJsonPath('data', $mockedTokenPair->toArray());
    }

    protected function getEndpointUrl(): string
    {
        return route('login');
    }

    protected function validateRequest(
        LoginPort $argInput,
        array $input
    ): bool {
        try {
            $this->assertSame($input['email'], $argInput->getUserEmail());
            $this->assertSame($input['password'], $argInput->getUserPassword());

            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }

    protected static function validRequestInput(): array
    {
        $faker = self::makeFaker();
        return [
            'email' => $faker->email(),
            'password' => $faker->sentence,
        ];
    }
}
