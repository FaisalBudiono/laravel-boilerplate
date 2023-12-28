<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\FailedParsingException;
use App\Exceptions\Http\UnauthorizedException;
use App\Exceptions\Models\ModelNotFoundException;
use App\Http\Middleware\AuthenticatedByJWT;
use App\Models\User\User;
use Exception;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthenticatedByJWTTest extends TestCase
{
    #[Test]
    #[DataProvider('invalidHeaderDataProvider')]
    public function should_throw_unauthorized_exception_when_no_authorization_token_provided(
        Request $mockRequest
    ): void {
        // Arrange
        $middleware = $this->makeMiddleware();


        try {
            // Act
            $middleware->handle(
                $mockRequest,
                function (Request $argRequest) {
                    return new Response();
                }
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Pre-Assert
            $expectedException = new UnauthorizedException(new ExceptionMessageStandard(
                'Authentication is needed to proceed',
                ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }

    public static function invalidHeaderDataProvider(): array
    {
        $notBearerToken = new Request();
        $notBearerToken->headers->set('authorization', 'something-else jaskdjalkdjal');

        return [
            'no authorization header' => [
                new Request(),
            ],

            'authorization is not bearer token' => [
                $notBearerToken,
            ],
        ];
    }


    #[Test]
    #[DataProvider('jwtExceptionDataProvider')]
    public function should_throw_unauthorized_exception_when_there_is_some_error_thrown(
        Exception $mockException,
        Exception $expectedException,
    ): void {
        // Arrange
        $mockedToken = $this->faker->words(10, true);

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', "Bearer {$mockedToken}");


        // Pre-Assert
        $mockJWTSigner = $this->mock(
            JWTSigner::class,
            function (MockInterface $mock) use ($mockedToken, $mockException) {
                $mock->shouldReceive('validate')
                    ->once()
                    ->with($mockedToken)
                    ->andThrow($mockException);
            }
        );
        assert($mockJWTSigner instanceof JWTSigner);


        try {
            // Act
            $this->makeMiddleware($mockJWTSigner)->handle(
                $mockRequest,
                function (Request $argRequest) {
                    return new Response();
                }
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->assertEquals($expectedException, $e);
        }
    }

    public static function jwtExceptionDataProvider(): array
    {
        return [
            'generic exception' => [
                $e = new Exception('some error'),
                new UnauthorizedException(new ExceptionMessageStandard(
                    'Failed to validate provided token',
                    ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
                ), $e),
            ],
            'JWT related exception' => [
                $e = new FailedParsingException(new ExceptionMessageStandard(
                    'JWT related error message',
                    'some error code'
                )),
                new UnauthorizedException(new ExceptionMessageStandard(
                    'JWT related error message',
                    'some error code'
                ), $e),
            ],
        ];
    }

    #[Test]
    public function should_throw_unauthorized_exception_when_user_is_not_found(): void
    {
        // Arrange
        $mockedToken = $this->faker->words(10, true);

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', "Bearer {$mockedToken}");


        // Assert
        $mockJWTSigner = $this->mock(
            JWTSigner::class,
            function (MockInterface $mock) use ($mockedToken) {
                $mock->shouldReceive('validate')
                    ->once()
                    ->with($mockedToken)
                    ->andReturnNull();
            }
        );
        assert($mockJWTSigner instanceof JWTSigner);

        $mockJWTParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedToken) {
                $mock->shouldReceive('parse')
                    ->once()
                    ->with($mockedToken)
                    ->andReturn(new Claims(
                        new ClaimsUser($this->faker->numerify(), $this->faker->email()),
                        collect(),
                        now(),
                        now(),
                        now(),
                    ));
            }
        );
        assert($mockJWTParser instanceof JWTParser);


        try {
            // Act
            $this->makeMiddleware($mockJWTSigner, $mockJWTParser)->handle(
                $mockRequest,
                function (Request $argRequest) {
                    return new Response();
                }
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $this->assertInstanceOf(ModelNotFoundException::class, $e->getPrevious());

            $expectedException = new UnauthorizedException(new ExceptionMessageStandard(
                'Failed to validate provided token',
                ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
            ), $e->getPrevious());
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    #[DataProvider('validHeaderSetupDataProvider')]
    public function should_return_callback_when_jwt_token_can_be_authenticated(
        string $headerName,
        string $tokenType
    ): void {
        // Arrange
        $mockUser = User::factory()->create();

        $mockedToken = $this->faker->words(10, true);

        $mockRequest = new Request();
        $mockRequest->headers->set($headerName, "{$tokenType} {$mockedToken}");

        $mockResponse = new Response();


        // Assert
        $mockJWTSigner = $this->mock(
            JWTSigner::class,
            function (MockInterface $mock) use ($mockedToken) {
                $mock->shouldReceive('validate')
                    ->once()
                    ->with($mockedToken)
                    ->andReturnNull();
            }
        );
        assert($mockJWTSigner instanceof JWTSigner);

        $mockJWTParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedToken, $mockUser) {
                $mock->shouldReceive('parse')
                    ->once()
                    ->with($mockedToken)
                    ->andReturn(new Claims(
                        new ClaimsUser((string)$mockUser->id, $this->faker->email()),
                        collect(),
                        now(),
                        now(),
                        now(),
                    ));
            }
        );
        assert($mockJWTParser instanceof JWTParser);


        // Act
        $result = $this->makeMiddleware($mockJWTSigner, $mockJWTParser)->handle(
            $mockRequest,
            function (Request $argRequest) use ($mockResponse) {
                return $mockResponse;
            }
        );


        // Assert
        $this->assertEquals($mockResponse, $result);
    }

    public static function validHeaderSetupDataProvider(): array
    {
        return [
            'header name with title case' => [
                'Authorization',
                'Bearer',
            ],
            'header name with all lowercase' => [
                'authorization',
                'Bearer',
            ],
            'token type with lowercase' => [
                'authorization',
                'bearer',
            ],
        ];
    }

    protected function makeMiddleware(
        ?JWTSigner $JWTSigner = null,
        ?JWTParser $JWTParser = null,
    ): AuthenticatedByJWT {
        if (is_null($JWTSigner)) {
            $JWTSigner = $this->mock(JWTSigner::class);
        }

        if (is_null($JWTParser)) {
            $JWTParser = $this->mock(JWTParser::class);
        }

        return new AuthenticatedByJWT($JWTSigner, $JWTParser);
    }
}
