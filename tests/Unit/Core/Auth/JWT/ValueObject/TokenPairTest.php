<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\ValueObject;

use App\Core\Auth\JWT\ValueObject\TokenPair;
use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TokenPairTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Arrange
        $valueObject = new TokenPair(
            $this->faker->sentence(),
            $this->faker->sentence(),
        );


        // Assert
        $this->assertInstanceOf(Arrayable::class, $valueObject);
    }

    #[Test]
    public function toArray_should_map_data_correctly(): void
    {
        // Arrange
        $valueObject = new TokenPair(
            $this->faker->sentence(),
            $this->faker->sentence(),
        );


        // Act
        $result = $valueObject->toArray();


        // Assert
        $expectedResult = [
            'type' => 'Bearer',
            'accessToken' => $valueObject->accessToken,
            'refreshToken' => $valueObject->refreshToken,
        ];
        $this->assertEquals($expectedResult, $result);
    }
}
