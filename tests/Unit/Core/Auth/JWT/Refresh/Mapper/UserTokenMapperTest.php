<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\Mapper;

use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapper;
use App\Core\Auth\JWT\Refresh\Mapper\UserTokenMapperContract;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTokenMapperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $now = now();
        Carbon::setTestNow($now);
    }

    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(UserTokenMapperContract::class, $this->makeService());
    }

    #[Test]
    public function should_return_claim_when_successfully_mapped_user(): void
    {
        // Arrange
        $mockedUuid = Str::freezeUuids();
        $user = User::factory()->create()->fresh();


        // Act
        $result = $this->makeService()->map($user);


        // Assert
        $expectedResult = new RefreshTokenClaims(
            $mockedUuid->toString(),
            new RefreshTokenClaimsUser((string)$user->id, $user->email),
            now()->addSeconds($this->getJWTRefreshTokenTTLInSeconds()),
        );
        $this->assertEquals($expectedResult, $result);
    }

    protected function getJWTRefreshTokenTTLInSeconds(): int
    {
        return intval(config('jwt.refresh.ttl'));
    }

    protected function makeService(): UserTokenMapper
    {
        return new UserTokenMapper();
    }
}
