<?php

namespace Tests\Unit\Core\Auth\JWT\Mapper;

use App\Core\Auth\JWT\Mapper\JWTMapper;
use App\Core\Auth\JWT\Mapper\JWTMapperContract;
use App\Core\Auth\JWT\ValueObject\Claims;
use App\Core\Auth\JWT\ValueObject\ClaimsUser;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JWTMapperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();
        Carbon::setTestNow($now);
    }

    #[Test]
    public function should_implement_right_interface()
    {
        // Assert
        $this->assertInstanceOf(JWTMapperContract::class, $this->makeService());
    }

    #[Test]
    public function should_map_user_data_successfully()
    {
        // Arrange
        $user = User::factory()->create()->fresh();


        // Act
        $result = $this->makeService()->map($user);


        // Assert
        $expectedResult = new Claims(
            new ClaimsUser($user->id, $user->email),
            collect($this->getAudience()),
            now()->subSecond(),
            now()->subSecond(),
            now()->addMinutes($this->getTTLInMinute()),
        );
        $this->assertEquals($expectedResult, $result);
    }

    protected function getAudience(): array
    {
        return config('jwt.audience');
    }

    protected function getTTLInMinute(): int
    {
        return config('jwt.ttl');
    }

    protected function makeService(): JWTMapper
    {
        return new JWTMapper;
    }
}
