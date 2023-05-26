<?php

namespace Tests\Unit\Core\Healthcheck\ValueObject;

use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthcheckResponseTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface()
    {
        // Arrange
        $valueObject = new HealthcheckResponse('asd');


        // Arrange
        $this->assertInstanceOf(Arrayable::class, $valueObject);
    }

    #[Test]
    public function toArray_should_map_value_object_to_arrray_correctly()
    {
        // Arrange
        $valueObject = new HealthcheckResponse(
            $version = $this->faker->word,
        );


        // Act
        $result = $valueObject->toArray();


        // Arrange
        $this->assertEquals([
            'version' => $version,
        ], $result);
    }
}
