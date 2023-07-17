<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_SetChildID_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_throw_exception_when_child_id_key_not_found()
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $mockedChildID = $this->faker->uuid();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:child:id")
            ->once()
            ->andReturn(false);

        $expectedException =  new InvalidTokenException(new ExceptionMessageStandard(
            'Refresh token not found',
            RefreshTokenExceptionCode::NOT_FOUND->value,
        ));
        $this->expectExceptionObject($expectedException);


        // Act
        $this->makeService()->setChildID($mockedID, $mockedChildID);
    }

    #[Test]
    public function should_throw_exception_when_expired_at_key_not_found()
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $mockedChildID = $this->faker->uuid();


        // Assert
        Cache::shouldReceive('has')
            ->with("{$this->getPrefixName()}:{$mockedID}:child:id")
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
        $this->makeService()->setChildID($mockedID, $mockedChildID);
    }

    #[Test]
    public function should_return_whether_token_is_used_or_not_when_key_value()
    {
        // Arrange
        $mockedID = $this->faker->uuid();
        $mockedChildID = $this->faker->uuid();
        $mockedExpiredAt = Carbon::parse($this->faker->dateTime);


        // Assert
        $isUnusedKey = "{$this->getPrefixName()}:{$mockedID}:child:id";
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
            ->andReturn($mockedExpiredAt->unix());

        Cache::shouldReceive('put')
            ->withArgs(function (
                string $argKey,
                string $argValue,
                Carbon $argExpiredAt,
            ) use ($isUnusedKey, $mockedExpiredAt, $mockedChildID) {
                $this->assertEquals($isUnusedKey, $argKey);
                $this->assertEquals($mockedChildID, $argValue);
                $this->assertEquals($mockedExpiredAt, $argExpiredAt);

                return true;
            })->once()
            ->andReturnNull();


        // Act
        $this->makeService()->setChildID($mockedID, $mockedChildID);
    }
}
