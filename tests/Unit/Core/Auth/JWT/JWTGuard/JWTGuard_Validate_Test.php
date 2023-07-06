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

class JWTGuard_Validate_Test extends JWTGuardBaseTestCase
{
    #[Test]
    #[DataProvider('wrongCredentialsDataProvider')]
    public function should_return_false_when_no_token_supplied(array $credentials)
    {
        // Arrange
        $service = $this->makeService();


        // Act
        $result = $service->validate($credentials);


        // Assert
        $this->assertFalse($result);
    }

    public static function wrongCredentialsDataProvider(): array
    {
        return [
            'no credentials at all' => [[]],
            'no token credential' => [
                [
                    'not-token' => 'some token',
                ],
            ],
        ];
    }

    #[Test]
    public function should_return_false_and_not_set_any_user_when_supplied_token_is_invalid()
    {
        // Arrange
        $mockedException = new Exception('some error');

        $mockedToken = $this->faker->regexify('[a-zA-Z0-9]{40}');

        $mockRequest = new Request();

        /** @var JWTParser */
        $mockJWTParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedToken, $mockedException) {
                $mock->shouldReceive('issue')
                    ->once()
                    ->with($mockedToken)
                    ->andThrow($mockedException);
            }
        );

        $service = $this->makeService($mockRequest, $mockJWTParser);


        // Act
        $result = $service->validate([
            'token' => $mockedToken,
        ]);

        $resultUser = $service->user();


        // Assert
        $this->assertFalse($result);

        $this->assertNull($resultUser);
    }

    #[Test]
    public function should_return_true_and_set_user_when_supplied_token_is_valid()
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        $mockedToken = $this->faker->regexify('[a-zA-Z0-9]{40}');

        $mockRequest = new Request();

        /** @var JWTParser */
        $mockJWTParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedToken, $mockedUser) {
                $mock->shouldReceive('issue')
                    ->once()
                    ->with($mockedToken)
                    ->andReturn(new Claims(
                        new ClaimsUser($mockedUser->id, $mockedUser->email),
                        collect(),
                        now(),
                        now(),
                        now(),
                    ));
            }
        );

        $service = $this->makeService($mockRequest, $mockJWTParser);


        // Act
        $result = $service->validate([
            'token' => $mockedToken,
        ]);

        $resultUser = $service->user();


        // Assert
        $this->assertTrue($result);

        $this->assertEquals($mockedUser, $resultUser);
    }
}
