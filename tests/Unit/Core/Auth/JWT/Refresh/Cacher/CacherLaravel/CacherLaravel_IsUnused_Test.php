<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\RefreshTokenExceptionCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Core\Auth\JWT\InvalidTokenException;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_IsUnused_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_throw_exception_when_token_is_not_found()
    {
        // Arrange
        $mockedID = $this->faker->uuid();

        $service = $this->makeService();


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
        $service->isUnused($mockedID);
    }

    #[Test]
    #[DataProvider('isUnusedDataProvider')]
    public function should_return_whether_token_is_used_or_not_when_key_value(mixed $mockedInvalidValue, bool $expectedResult)
    {
        // Arrange
        $mockedID = $this->faker->uuid();

        $service = $this->makeService();


        // Assert
        $isUnusedKey = "{$this->getPrefixName()}:{$mockedID}:is-unused";
        Cache::shouldReceive('has')
            ->with($isUnusedKey)
            ->once()
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->with($isUnusedKey)
            ->once()
            ->andReturn($mockedInvalidValue);


        // Act
        $result = $service->isUnused($mockedID);


        // Assert
        $this->assertSame($expectedResult, $result);
    }

    public static function isUnusedDataProvider(): array
    {
        return [
            '1 as string' => [
                '1',
                false,
            ],
            '1 as number' => [
                1,
                false,
            ],

            'more than 1 as string' => [
                '123123',
                false,
            ],
            'more than 1 as number' => [
                123123,
                false,
            ],

            '-1 as string' => [
                '-1',
                false,
            ],
            '-1 as number' => [
                -1,
                false,
            ],

            'less than 0 as string' => [
                '-123123',
                false,
            ],
            'less than 0 as number' => [
                -123123,
                false,
            ],

            'null' => [
                null,
                false,
            ],

            '0 as string' => [
                '0',
                true,
            ],
            '0 as number' => [
                0,
                true,
            ],
        ];
    }
}
