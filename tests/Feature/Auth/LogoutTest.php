<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Core\Auth\AuthJWTCoreContract;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\FailedParsingException;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use App\Exceptions\Core\Auth\JWT\JWTException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Exceptions\Http\UnauthorizedException;
use App\Port\Core\Auth\LogoutPort;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\BaseFeatureTestCase;

class LogoutTest extends BaseFeatureTestCase
{
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
    public function should_show_500_when_generic_error_is_thrown(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $input = $this->validRequestInput();

        $mockException = new \Error($this->faker->sentence());


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('logout')
                    ->once()
                    ->withArgs(fn (
                        LogoutPort $argInput,
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockException);
            }
        );
        $this->instance(AuthJWTCoreContract::class, $mockCore);


        try {
            // Act
            $this->postJson(
                $this->getEndpointUrl(),
                $input,
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
    #[DataProvider('jwtExceptionDataProvider')]
    public function should_show_401_when_jwt_exception_is_thrown(JWTException $mockJWTException): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $input = $this->validRequestInput();

        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input, $mockJWTException) {
                $mock->shouldReceive('logout')
                    ->once()
                    ->withArgs(fn (
                        LogoutPort $argInput,
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockJWTException);
            }
        );
        $this->instance(AuthJWTCoreContract::class, $mockCore);


        try {
            // Act
            $this->postJson(
                $this->getEndpointUrl(),
                $input,
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new UnauthorizedException(
                $mockJWTException->exceptionMessage,
                $mockJWTException,
            );
            $this->assertEquals($expectedException, $e);
        }
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
    public function should_show_204_no_content_when_successfully_logout(): void
    {
        // Arrange
        $input = $this->validRequestInput();


        // Assert
        $mockCore = $this->mock(
            AuthJWTCoreContract::class,
            function (MockInterface $mock) use ($input) {
                $mock->shouldReceive('logout')
                    ->once()
                    ->withArgs(fn (
                        LogoutPort $argInput,
                    ) => $this->validateRequest($argInput, $input));
            }
        );
        $this->instance(AuthJWTCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input,
        );


        // Assert
        $response->assertNoContent();
    }

    protected function getEndpointUrl(): string
    {
        return route('logout');
    }

    protected function validateRequest(
        LogoutPort $argInput,
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
