<?php

namespace Tests\Unit\Http\Middleware;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\FailedParsingException;
use App\Exceptions\Http\UnauthorizedException;
use App\Http\Middleware\AuthenticatedByJWT;
use App\Models\User\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthenticatedByJWTTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('invalidHeaderDataProvider')]
    public function should_throw_unauthorized_exception_when_no_authorization_token_provided(
        Request $mockRequest
    ) {
        // Arrange
        $middleware = $this->makeMiddleware();


        // Pre-Assert
        $expectedException = new UnauthorizedException(new ExceptionMessageStandard(
            'Authentication is needed to proceed',
            ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $middleware->handle(
            $mockRequest,
            function (Request $argRequest) {
                return new Response();
            }
        );
    }

    public static function invalidHeaderDataProvider(): array
    {
        $notBearerToken = new Request();
        $notBearerToken->headers->set('authentication', 'something-else jaskdjalkdjal');

        return [
            'no authentication header' => [
                new Request(),
            ],

            'authentication is not bearer token' => [
                $notBearerToken,
            ],
        ];
    }


    #[Test]
    #[DataProvider('jwtExceptionDataProvider')]
    public function should_throw_unauthorized_exception_when_there_is_some_error_thrown(
        Exception $mockException,
        Exception $expectedException,
    ) {
        // Arrange
        $mockedToken = $this->faker->words(10, true);

        $mockRequest = new Request();
        $mockRequest->headers->set('Authentication', "Bearer {$mockedToken}");


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

        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeMiddleware($mockJWTSigner)->handle(
            $mockRequest,
            function (Request $argRequest) {
                return new Response();
            }
        );
    }

    public static function jwtExceptionDataProvider(): array
    {
        return [
            'generic exception' => [
                new Exception('some error'),
                new UnauthorizedException(new ExceptionMessageStandard(
                    'Failed to validate provided token',
                    ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
                )),
            ],
            'JWT related exception' => [
                new FailedParsingException(new ExceptionMessageStandard(
                    'JWT related error message',
                    'some error code'
                )),
                new UnauthorizedException(new ExceptionMessageStandard(
                    'JWT related error message',
                    'some error code'
                )),
            ],
        ];
    }

    #[Test]
    public function should_throw_unauthorized_exception_when_user_is_not_found()
    {
        // Arrange
        $mockedToken = $this->faker->words(10, true);

        $mockRequest = new Request();
        $mockRequest->headers->set('Authentication', "Bearer {$mockedToken}");


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
                        new ClaimsUser($this->faker->numerify, $this->faker->email()),
                        collect(),
                        now(),
                        now(),
                        now(),
                    ));
            }
        );
        assert($mockJWTParser instanceof JWTParser);


        // Pre-Assert
        $expectedException = new UnauthorizedException(new ExceptionMessageStandard(
            'Failed to validate provided token',
            ExceptionErrorCode::AUTHENTICATION_NEEDED->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeMiddleware($mockJWTSigner, $mockJWTParser)->handle(
            $mockRequest,
            function (Request $argRequest) {
                return new Response();
            }
        );
    }

    #[Test]
    #[DataProvider('validHeaderSetupDataProvider')]
    public function should_return_callback_when_jwt_token_can_be_authenticated(
        string $headerName,
        string $tokenType
    ) {
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
                        new ClaimsUser($mockUser->id, $this->faker->email()),
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
                'Authentication',
                'Bearer',
            ],
            'header name with all lowercase' => [
                'authentication',
                'Bearer',
            ],
            'token type with lowercase' => [
                'authentication',
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
