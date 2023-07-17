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

class JWTGuard_Check_Test extends JWTGuardBaseTestCase
{
    #[Test]
    #[DataProvider('noAuthorizationHeaderDataProvider')]
    public function should_return_false_when_no_authorization_header(Request $mockRequest)
    {
        // Act
        $result = $this->makeService($mockRequest)->check();


        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    #[DataProvider('notBearerTokenDataProvider')]
    public function should_return_false_when_token_in_authorization_header_is_not_bearer_token(
        string $mockedToken,
    ) {
        // Arrange
        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', $mockedToken);


        // Act
        $result = $this->makeService($mockRequest)->check();


        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function should_return_false_when_there_is_some_error_thrown()
    {
        // Arrange
        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);

        $mockedException = new Exception($this->faker->sentence);


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
        $result = $this->makeService($mockRequest, null, $mockJWTSigner)->check();


        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function should_return_true_and_set_logged_in_user_when_token_is_valid()
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);


        // Assert
        $mockJwtParser = $this->mock(
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
        assert($mockJwtParser instanceof JWTParser);

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
        $result = $this->makeService($mockRequest, $mockJwtParser, $mockJWTSigner)->check();


        // Assert
        $this->assertTrue($result);
    }
}
