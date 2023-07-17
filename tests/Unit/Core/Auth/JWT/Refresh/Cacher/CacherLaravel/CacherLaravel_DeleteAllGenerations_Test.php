<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_DeleteAllGenerations_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_delete_token_without_any_child()
    {
        // Arrange
        $mockToken1 = $this->faker->uuid();


        // Assert
        Cache::shouldReceive('get')
            ->once()
            ->with($this->getChildIDKey($mockToken1))
            ->andReturnNull();

        Cache::shouldReceive('forget')
            ->once()
            ->with($this->getExpiredAtKey($mockToken1));
        Cache::shouldReceive('forget')
            ->once()
            ->with($this->getChildIDKey($mockToken1));
        Cache::shouldReceive('forget')
            ->once()
            ->with($this->getIsUnusedKey($mockToken1));
        Cache::shouldReceive('forget')
            ->once()
            ->with($this->getUserIDKey($mockToken1));
        Cache::shouldReceive('forget')
            ->once()
            ->with($this->getUserEmailKey($mockToken1));


        // Act
        $this->makeService()->deleteAllGenerations($mockToken1);
    }

    #[Test]
    #[DataProvider('tokenGenerationDataProvider')]
    public function should_delete_token_with_some_children(Collection $mockTokenIDs)
    {
        // Assert
        $mockTokenIDs->each(function (string $tokenID, int $index) use ($mockTokenIDs) {
            $nextChildTokenID = $mockTokenIDs[$index + 1] ?? null;
            Cache::shouldReceive('get')
                ->once()
                ->with($this->getChildIDKey($tokenID))
                ->andReturn($nextChildTokenID);

            Cache::shouldReceive('forget')
                ->once()
                ->with($this->getExpiredAtKey($tokenID));
            Cache::shouldReceive('forget')
                ->once()
                ->with($this->getChildIDKey($tokenID));
            Cache::shouldReceive('forget')
                ->once()
                ->with($this->getIsUnusedKey($tokenID));
            Cache::shouldReceive('forget')
                ->once()
                ->with($this->getUserIDKey($tokenID));
            Cache::shouldReceive('forget')
                ->once()
                ->with($this->getUserEmailKey($tokenID));
        });


        // Act
        $this->makeService()->deleteAllGenerations($mockTokenIDs[0]);
    }

    public static function tokenGenerationDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            '2 generation' => [collect([
                $faker->uuid(),
                $faker->uuid(),
            ])],
            '3 generation' => [collect([
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
            ])],
            '10 generation' => [collect([
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
                $faker->uuid(),
            ])],
        ];
    }

    protected function getExpiredAtKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:expired-at";
    }

    protected function getChildIDKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:child:id";
    }

    protected function getIsUnusedKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:is-unused";
    }

    protected function getUserIDKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:user:id";
    }

    protected function getUserEmailKey(string $tokenID): string
    {
        return "{$this->getPrefixName()}:{$tokenID}:user:email";
    }
}
