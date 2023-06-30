<?php

namespace Tests\Unit\Core\Healthcheck\ValueObject;

use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\ValueObject\HealthcheckStatus;
use Exception;
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
    public function toArray_should_map_value_object_to_arrray_correctly_when_no_dependency_status()
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
            'isHealthy' => true,
            'dependencies' => [],
        ], $result);
    }

    #[Test]
    public function toArray_should_map_value_object_to_arrray_correctly_when_all_status_is_healthy()
    {
        // Arrange
        $valueObject = new HealthcheckResponse(
            $version = $this->faker->word,
            new HealthcheckStatus('mysql', null),
            new HealthcheckStatus('redis', null),
        );


        // Act
        $result = $valueObject->toArray();


        // Arrange
        $this->assertEquals([
            'version' => $version,
            'isHealthy' => true,
            'dependencies' => [
                [
                    'name' => 'mysql',
                    'isHealthy' => true,
                    'reason' => null,
                ],
                [
                    'name' => 'redis',
                    'isHealthy' => true,
                    'reason' => null,
                ],
            ]
        ], $result);
    }

    #[Test]
    public function toArray_should_map_value_object_to_arrray_correctly_when_all_status_is_bad()
    {
        // Arrange
        $mockMysqlError = new Exception('mysql error');
        $mockRedisError = new Exception('redis error');

        $valueObject = new HealthcheckResponse(
            $version = $this->faker->word,
            new HealthcheckStatus('mysql', $mockMysqlError),
            new HealthcheckStatus('redis', $mockRedisError),
        );


        // Act
        $result = $valueObject->toArray();


        // Arrange
        $this->assertEquals([
            'version' => $version,
            'isHealthy' => false,
            'dependencies' => [
                [
                    'name' => 'mysql',
                    'isHealthy' => false,
                    'reason' => $mockMysqlError->getMessage(),
                ],
                [
                    'name' => 'redis',
                    'isHealthy' => false,
                    'reason' => $mockRedisError->getMessage(),
                ],
            ]
        ], $result);
    }
}
