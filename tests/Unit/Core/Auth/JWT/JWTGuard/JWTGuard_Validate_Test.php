<?php

declare(strict_types=1);

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

class JWTGuard_Validate_Test extends JWTGuardBaseTestCase
{
    #[Test]
    #[DataProvider('wrongCredentialsDataProvider')]
    public function should_return_false_when_no_token_supplied(array $credentials): void
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
    public function should_return_false_and_not_set_any_user_when_there_is_some_error_thrown(): void
    {
        // Arrange
        $mockedException = new Exception($this->faker->sentence);

        $mockedToken = $this->faker->regexify('[a-zA-Z0-9]{40}');

        $mockRequest = new Request();


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
        $service = $this->makeService($mockRequest, null, $mockJWTSigner);

        $result = $service->validate([
            'token' => $mockedToken,
        ]);

        $resultUser = $service->user();


        // Assert
        $this->assertFalse($result);

        $this->assertNull($resultUser);
    }

    #[Test]
    public function should_return_true_and_set_user_when_supplied_token_is_valid(): void
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        $mockedToken = $this->faker->regexify('[a-zA-Z0-9]{40}');

        $mockRequest = new Request();

        // Assert
        $mockJWTParser = $this->mock(
            JWTParser::class,
            function (MockInterface $mock) use ($mockedToken, $mockedUser) {
                $mock->shouldReceive('parse')
                    ->once()
                    ->with($mockedToken)
                    ->andReturn(new Claims(
                        new ClaimsUser((string)$mockedUser->id, $mockedUser->email),
                        collect(),
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

        $result = $service->validate([
            'token' => $mockedToken,
        ]);

        $resultUser = $service->user();


        // Assert
        $this->assertTrue($result);

        $this->assertEquals($mockedUser, $resultUser);
    }
}
