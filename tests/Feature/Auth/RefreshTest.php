<?php

namespace Tests\Feature\Auth;

use App\Core\Auth\AuthJWTCoreContract;
use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\FailedParsingException;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use App\Exceptions\Core\Auth\JWT\JWTException;
use App\Port\Core\Auth\GetRefreshTokenPort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\BaseFeatureTestCase;

class RefreshTest extends BaseFeatureTestCase
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
        $faker = self::makeFaker();

        return [
            'without refreshToken' => [
                'refreshToken',
                collect(self::validRequestInput())
                    ->except('refreshToken')
                    ->toArray(),
            ],
            'refreshToken is not string (now contain null)' => [
                'refreshToken',
                collect(self::validRequestInput())
                    ->replace([
                        'refreshToken' => null,
                    ])->toArray(),
            ],
            'refreshToken is not string (now contain number)' => [
                'refreshToken',
                collect(self::validRequestInput())
                    ->replace([
                        'refreshToken' => $faker->numberBetween(),
                    ])->toArray(),
            ],
            'refreshToken is not string (now contain array)' => [
                'refreshToken',
                collect(self::validRequestInput())
                    ->replace([
                        'refreshToken' => [$faker->words(3, true)],
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
        $mockException = new \Error($this->faker->sentence());


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('refresh')
                    ->once()
                    ->withArgs(fn (
                        GetRefreshTokenPort $argInput,
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
            $exceptionMessage->getJsonResponse()->toArray(),
        );
    }

    #[Test]
    #[DataProvider('jwtExceptionDataProvider')]
    public function should_show_401_when_jwt_exception_is_thrown(
        JWTException $mockJWTException
    ) {
        // Arrange
        $input = $this->validRequestInput();


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockJWTException) {
                $mock->shouldReceive('refresh')
                    ->once()
                    ->withArgs(fn (
                        GetRefreshTokenPort $argInput,
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockJWTException);
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
            $mockJWTException->exceptionMessage->getJsonResponse()->toArray(),
        );
    }

    public static function jwtExceptionDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'InvalidTokenException' => [
                new InvalidTokenException(new ExceptionMessageStandard(
                    $faker->sentence,
                    $faker->sentence,
                )),
            ],
            'FailedParsingException' => [
                new FailedParsingException(new ExceptionMessageStandard(
                    $faker->sentence,
                    $faker->sentence,
                )),
            ],
        ];
    }

    #[Test]
    public function should_show_200_when_token_is_successfully_recreated()
    {
        // Arrange
        $input = $this->validRequestInput();

        $mockedToken = new TokenPair(
            $this->faker->sentence,
            $this->faker->sentence,
        );


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedToken) {
                $mock->shouldReceive('refresh')
                    ->once()
                    ->withArgs(fn (
                        GetRefreshTokenPort $argInput,
                    ) => $this->validateRequest($argInput, $input))
                    ->andReturn($mockedToken);
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
        $response->assertJsonPath(
            'data',
            $mockedToken->toArray()
        );
    }

    protected function getEndpointUrl(): string
    {
        return route('refresh');
    }

    protected function validateRequest(
        GetRefreshTokenPort $argInput,
        array $input
    ): bool {
        try {
            $this->assertSame($input['refreshToken'], $argInput->getRefreshToken());

            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }

    protected static function validRequestInput(): array
    {
        $faker = self::makeFaker();

        return [
            'refreshToken' => $faker->uuid(),
        ];
    }
}
