<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Mapper;

use App\Core\Auth\JWT\Mapper\JWTMapper;
use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Models\User\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JWTMapperTest extends TestCase
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
        $this->assertInstanceOf(JWTMapperContract::class, $this->makeService());
    }

    #[Test]
    public function should_map_user_data_successfully(): void
    {
        // Arrange
        $user = User::factory()->create()->fresh();


        // Act
        $result = $this->makeService()->map($user);


        // Assert
        $expectedResult = new Claims(
            new ClaimsUser((string)$user->id, $user->email),
            collect($this->getAudience()),
            now()->subSecond(),
            now()->subSecond(),
            now()->addSeconds($this->getTTLInSeconds()),
        );
        $this->assertEquals($expectedResult, $result);
    }

    protected function getAudience(): array
    {
        return config('jwt.audience');
    }

    protected function getTTLInSeconds(): int
    {
        return intval(config('jwt.ttl'));
    }

    protected function makeService(): JWTMapper
    {
        return new JWTMapper();
    }
}
