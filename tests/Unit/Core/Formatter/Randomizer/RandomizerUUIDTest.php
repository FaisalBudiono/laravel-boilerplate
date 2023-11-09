<?php

namespace Tests\Unit\Core\Formatter\Randomizer;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Formatter\Randomizer\RandomizerUUID;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RandomizerUUIDTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(Randomizer::class, $this->makeService());
    }

    #[Test]
    public function getRandomizeString_should_randomized_uuid(): void
    {
        // Arrange
        $service = $this->makeService();


        // Act
        $result = $service->getRandomizeString();


        // Assert
        $this->assertTrue(Str::isUuid($result));
    }

    protected function makeService(): RandomizerUUID
    {
        return new RandomizerUUID;
    }
}
