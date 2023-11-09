<?php

namespace Tests\Unit\Core\Auth\JWT\JWTGuard;

use App\Core\Auth\JWT\Parser\JWTParser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Models\User\User;
use Exception;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class JWTGuard_Guest_Test extends JWTGuardBaseTestCase
{
    #[Test]
    #[DataProvider('noAuthorizationHeaderDataProvider')]
    public function should_return_true_when_no_authorization_header(Request $mockRequest): void
    {
        // Act
        $result = $this->makeService($mockRequest)->guest();


        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[DataProvider('notBearerTokenDataProvider')]
    public function should_return_true_when_token_in_authorization_header_is_not_bearer_token(
        string $mockedToken,
    ): void {
        // Arrange
        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', $mockedToken);


        // Act
        $result = $this->makeService($mockRequest)->guest();


        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function should_return_true_and_not_set_any_user_when_there_is_some_error_thrown(): void
    {
        // Arrange
        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);

        $mockedException = new Exception($this->faker->sentence());


        // Assert
        $mockJWTSigner = $this->mock(
            JWTSigner::class,
            function (MockInterface $mock) use ($mockedException, $mockedToken) {
                $mock->shouldReceive('validate')
                    ->once()
                    ->with($mockedToken)
                    ->andThrow($mockedException);
            }
        );
        assert($mockJWTSigner instanceof JWTSigner);


        // Act
        $result = $this->makeService($mockRequest, null, $mockJWTSigner)->guest();


        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function should_return_false_and_set_logged_in_user_when_jwt_token_is_valid(): void
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);

        $mockJWTParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedToken, $mockedUser) {
                $mock->shouldReceive('parse')
                    ->once()
                    ->with($mockedToken)
                    ->andReturn(new Claims(
                        new ClaimsUser($mockedUser->id, $mockedUser->email),
                        collect([]),
                        now(),
                        now(),
                        now(),
                    ));
            }
        );
        assert($mockJWTParser instanceof JWTParser);

        $mockJWTSigner = $this->mock(
            JWTSigner::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('validate')
                    ->once()
                    ->andReturnNull();
            }
        );
        assert($mockJWTSigner instanceof JWTSigner);


        // Act
        $result = $this->makeService(
            $mockRequest,
            $mockJWTParser,
            $mockJWTSigner,
        )->guest();


        // Assert
        $this->assertFalse($result);
    }
}
