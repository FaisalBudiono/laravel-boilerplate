<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_Invalidate_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_throw_exception_when_is_unused_key_is_not_found()
    {
        // Arrange
        $mockedID = $this->faker->uuid();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:is-unused")
            ->once()
            ->andReturn(false);

        $expectedException =  new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token not found',
            RefreshTokenExceptionCode::NOT_FOUND->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeService()->invalidate($mockedID);
    }

    #[Test]
    public function should_throw_exception_when_expired_at_key_is_not_found()
    {
        // Arrange
        $mockedID = $this->faker->uuid();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:is-unused")
            ->once()
            ->andReturn(true);

        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:expired-at")
            ->once()
            ->andReturn(false);

        $expectedException =  new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token not found',
            RefreshTokenExceptionCode::NOT_FOUND->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeService()->invalidate($mockedID);
    }

    #[Test]
    #[DataProvider('expiredAtValueDataProvider')]
    public function should_mark_that_token_is_used(mixed $mockedExpiredAtUnix)
    {
        // Arrange
        $mockedID = $this->faker->uuid();


        // Assert
        $isUnusedKey = "{$this->getPrefixName()}:{$mockedID}:is-unused";
        Cache::shouldReceive('has')
            ->with($isUnusedKey)
            ->once()
            ->andReturn(true);

        $expiredAtKey = "{$this->getPrefixName()}:{$mockedID}:expired-at";
        Cache::shouldReceive('has')
            ->with($expiredAtKey)
            ->once()
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->with($expiredAtKey)
            ->once()
            ->andReturn($mockedExpiredAtUnix);

        $expectedExpiredAt =  Carbon::parse(intval($mockedExpiredAtUnix));
        Cache::shouldReceive('put')
            ->withArgs(function (
                string $argKey,
                int $argValue,
                Carbon $argExpiredAt,
            ) use ($expectedExpiredAt, $isUnusedKey) {
                $this->assertEquals($isUnusedKey, $argKey);
                $this->assertEquals(1, $argValue);
                $this->assertEquals($expectedExpiredAt, $argExpiredAt);
                return true;
            })
            ->once();


        // Act
        $this->makeService()->invalidate($mockedID);
    }

    public static function expiredAtValueDataProvider(): array
    {
        $expiredAt = Carbon::parse(self::makeFaker()->dateTime);

        return [
            'unix as integer' => [$expiredAt->unix()],
            'unix as string' => [(string) $expiredAt->unix()],
            'null' => [null],
        ];
    }
}
