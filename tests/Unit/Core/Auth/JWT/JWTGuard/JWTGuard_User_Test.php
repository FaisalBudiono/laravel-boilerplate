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

class JWTGuard_User_Test extends JWTGuardBaseTestCase
{
    #[Test]
    #[DataProvider('noAuthorizationHeaderDataProvider')]
    public function should_return_null_when_no_authorization_header(Request $mockRequest): void
    {
        // Arrange
        $service = $this->makeService($mockRequest);


        // Act
        $result = $service->user();


        // Assert
        $this->assertNull($result);
    }

    #[Test]
    #[DataProvider('notBearerTokenDataProvider')]
    public function should_return_null_when_token_in_authorization_header_is_not_bearer_token(
        string $mockedToken,
    ): void {
        // Arrange
        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', $mockedToken);


        // Act
        $result = $this->makeService($mockRequest)->user();


        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function should_return_null_and_not_set_any_user_when_there_is_some_error_thrown(): void
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
        $result = $this->makeService($mockRequest, null, $mockJWTSigner)->user();


        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function should_return_user_and_set_logged_in_user_when_jwt_token_is_valid(): void
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);


        // Assert
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
        )->user();


        // Assert
        $this->assertEquals($mockedUser, $result);
    }

    #[Test]
    public function should_return_use_memoization_so_when_the_user_is_already_fetched_it_will_not_refetched_it_from_jwt_token(): void
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);


        // Assert
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
        $service = $this->makeService($mockRequest, $mockJWTParser, $mockJWTSigner);

        $service->user();
        $result = $service->user();


        // Assert
        $this->assertEquals($mockedUser, $result);
    }
}
