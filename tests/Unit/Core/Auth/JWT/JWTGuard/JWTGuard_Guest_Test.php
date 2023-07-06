<?php

namespace Tests\Unit\Core\Auth\JWT\JWTGuard;

use App\Core\Auth\JWT\Parser\JWTParser;
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
    public function should_return_true_when_no_authorization_header(Request $mockRequest)
    {
        // Arrange
        $service = $this->makeService($mockRequest);


        // Act
        $result = $service->guest();


        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    #[DataProvider('notBearerTokenDataProvider')]
    public function should_return_true_when_token_in_authorization_header_is_not_bearer_token(
        string $mockedToken,
    ) {
        // Arrange
        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', $mockedToken);

        $service = $this->makeService($mockRequest);


        // Act
        $result = $service->guest();


        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function should_return_true_and_not_set_any_user_when_jwt_parser_is_throwing_exception()
    {
        // Arrange
        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);

        $mockedException = new Exception('some error');

        /** @var JWTParser */
        $mockJwtParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedException, $mockedToken) {
                $mock->shouldReceive('issue')
                    ->once()
                    ->with($mockedToken)
                    ->andThrow($mockedException);
            }
        );

        $service = $this->makeService($mockRequest, $mockJwtParser);


        // Act
        $result = $service->guest();


        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function should_return_false_and_set_logged_in_user_when_jwt_parser_can_parse_token()
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        $mockedToken = 'xxxxxxxxx';

        $mockRequest = new Request();
        $mockRequest->headers->set('Authorization', 'bearer ' . $mockedToken);

        /** @var JWTParser */
        $mockJwtParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedToken, $mockedUser) {
                $mock->shouldReceive('issue')
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

        $service = $this->makeService($mockRequest, $mockJwtParser);


        // Act
        $result = $service->guest();


        // Assert
        $this->assertFalse($result);
    }
}
