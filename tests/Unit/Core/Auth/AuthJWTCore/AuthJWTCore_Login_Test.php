<?php

namespace Tests\Unit\Core\Auth\AuthJWTCore;

use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\Refresh\RefreshTokenManagerContract;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Core\Auth\JWT\Signer\JWTSigner;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Core\Auth\JWT\ValueObject\TokenPair;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\InvalidCredentialException;
use App\Models\User\Enum\UserExceptionCode;
use App\Models\User\User;
use App\Port\Core\Auth\LoginPort;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class AuthJWTCore_Login_Test extends AuthJWTCoreBaseTestCase
{
    protected LoginPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(LoginPort::class);
    }

    #[Test]
    public function should_throw_invalid_credential_exception_when_email_is_not_found(): void
    {
        // Arrange
        $user = User::factory()->create()->fresh();
        $notFoundEmail = $user->email . 'notemail';


        // Assert
        $this->mockRequest->shouldReceive('getUserEmail')->once()->andReturn($notFoundEmail);

        $expectedException = new InvalidCredentialException(new ExceptionMessageStandard(
            'Credential is invalid',
            UserExceptionCode::INVALID_CREDENTIAL->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeService()->login($this->mockRequest);
    }

    #[Test]
    public function should_throw_invalid_credential_exception_when_password_is_invalid(): void
    {
        // Arrange
        $user = User::factory()->create()->fresh();
        $invalidPassword = $this->faker->words(3, true);


        // Assert
        $this->mockRequest->shouldReceive('getUserEmail')->once()->andReturn($user->email);
        $this->mockRequest->shouldReceive('getUserPassword')->once()->andReturn($invalidPassword);

        Hash::shouldReceive('check')
            ->once()
            ->with($invalidPassword, $user->password)
            ->andReturn(false);

        $expectedException = new InvalidCredentialException(new ExceptionMessageStandard(
            'Credential is invalid',
            UserExceptionCode::INVALID_CREDENTIAL->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeService()->login($this->mockRequest);
    }

    #[Test]
    public function should_return_token_pair_when_successfully_authenticate_user(): void
    {
        // Arrange
        $user = User::factory()->create()->fresh();
        $validPassword = $this->faker->words(3, true);


        // Assert
        $this->mockRequest->shouldReceive('getUserEmail')->once()->andReturn($user->email);
        $this->mockRequest->shouldReceive('getUserPassword')->once()->andReturn($validPassword);

        Hash::shouldReceive('check')
            ->once()
            ->with($validPassword, $user->password)
            ->andReturn(true);

        $mockedClaims = $this->makeFakeClaims();
        $mockJWTMapper = $this->mock(
            JWTMapperContract::class,
            function (MockInterface $mock) use ($mockedClaims, $user) {
                $mock->shouldReceive('map')
                    ->once()
                    ->withArgs(function (User $argUser) use ($user) {
                        $this->assertTrue($argUser->is($user));
                        return true;
                    })->andReturn($mockedClaims);
            }
        );
        assert($mockJWTMapper instanceof JWTMapperContract);

        $mockedAccessToken = $this->faker->sentence();
        $mockJWTSigner = $this->mock(
            JWTSigner::class,
            function (MockInterface $mock) use ($mockedClaims, $mockedAccessToken) {
                $mock->shouldReceive('sign')
                    ->once()
                    ->with($mockedClaims)
                    ->andReturn($mockedAccessToken);
            }
        );
        assert($mockJWTSigner instanceof JWTSigner);

        $mockedRefreshTokenClaims = $this->makeFakeRefreshTokenClaims();
        $mockRefreshTokenManager = $this->mock(
            RefreshTokenManagerContract::class,
            function (MockInterface $mock) use ($user, $mockedRefreshTokenClaims) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(function (User $argUser) use ($user) {
                        $this->assertTrue($argUser->is($user));
                        return true;
                    })->andReturn($mockedRefreshTokenClaims);
            }
        );
        assert($mockRefreshTokenManager instanceof RefreshTokenManagerContract);


        // Act
        $result = $this->makeService(
            $mockJWTMapper,
            $mockJWTSigner,
            $mockRefreshTokenManager,
        )->login($this->mockRequest);


        // Assert
        $expectedResult = new TokenPair($mockedAccessToken, $mockedRefreshTokenClaims->id);

        $this->assertEquals($expectedResult, $result);
    }

    protected function makeFakeClaims(): Claims
    {
        return new Claims(
            new ClaimsUser($this->faker->uuid(), $this->faker->email),
            collect([
                $this->faker->sentence()
            ]),
            Carbon::parse($this->faker->dateTime),
            Carbon::parse($this->faker->dateTime),
            Carbon::parse($this->faker->dateTime),
        );
    }

    protected function makeFakeRefreshTokenClaims(): RefreshTokenClaims
    {
        return new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email),
            Carbon::parse($this->faker->dateTime),
            $this->faker->uuid(),
        );
    }
}
