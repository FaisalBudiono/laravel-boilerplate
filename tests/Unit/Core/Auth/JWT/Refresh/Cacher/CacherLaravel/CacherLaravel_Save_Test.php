<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaims;
use App\Core\Auth\JWT\Refresh\ValueObject\RefreshTokenClaimsUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_Save_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_cache_all_info()
    {
        // Arrange
        $mockedToken = new RefreshTokenClaims(
            $this->faker->uuid(),
            new RefreshTokenClaimsUser($this->faker->uuid(), $this->faker->email),
            Carbon::parse($this->faker->dateTimeBetween('1 years', '10 years')),
        );


        // Assert
        Cache::shouldReceive('putMany')
            ->with(
                [
                    "{$this->getPrefixName()}:{$mockedToken->id}:user:id" => $mockedToken->user->id,
                    "{$this->getPrefixName()}:{$mockedToken->id}:user:email" => $mockedToken->user->userEmail,
                    "{$this->getPrefixName()}:{$mockedToken->id}:child:id" => $mockedToken->childID,
                    "{$this->getPrefixName()}:{$mockedToken->id}:is-unused" => 0,
                    "{$this->getPrefixName()}:{$mockedToken->id}:expired-at" => $mockedToken->expiredAt->unix(),
                ],
                $mockedToken->expiredAt,
            )->once();


        // Act
        $this->makeService()->save($mockedToken);
    }
}
