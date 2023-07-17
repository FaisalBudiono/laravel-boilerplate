<?php

namespace Tests\Unit\Core\Healthcheck\ValueObject;

use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\ValueObject\HealthcheckStatus;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\Attributes\DataProvider;
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
    #[DataProvider('healthinessStatusDataProvider')]
    public function isHealthy_should_return_true_or_false_depending_of_the_dependencies_healthiness_status(
        HealthcheckResponse $valueObject,
        bool $expectedHealthiness,
    ) {
        // Act
        $result = $valueObject->isHealthy();


        // Arrange
        $this->assertSame($expectedHealthiness, $result);
    }

    public static function healthinessStatusDataProvider(): array
    {
        return [
            'dependency is healthy (with one dependency)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', null),
                ),
                true,
            ],
            'all dependencies is healthy (with multiple dependencies)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', null),
                    new HealthcheckStatus('redis', null),
                ),
                true,
            ],

            'one of dependency is NOT healthy (with multiple dependencies)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', new Exception('asd')),
                    new HealthcheckStatus('redis', null),
                ),
                false,
            ],
            'all dependencies is NOT healthy (with multiple dependencies)' => [
                new HealthcheckResponse(
                    'v1.0.0',
                    new HealthcheckStatus('mysql', new Exception('asd')),
                    new HealthcheckStatus('redis', new Exception('asd')),
                ),
                false,
            ],
        ];
    }

    #[Test]
    #[DataProvider('healthcheckStatusDataProvider')]
    public function toArray_should_map_value_object_to_array_correctly(
        string $mockVersion,
        array $healthcheckStatuses,
        array $expectedResult,
    ) {
        // Arrange
        $valueObject = new HealthcheckResponse(
            $mockVersion,
            ...$healthcheckStatuses
        );


        // Act
        $result = $valueObject->toArray();


        // Arrange
        $this->assertEquals($expectedResult, $result);
    }

    public static function healthcheckStatusDataProvider(): array
    {
        $faker = self::makeFaker();

        $version = $faker->sentence();

        $dependencyHealthy1 = new HealthcheckStatus($faker->sentence(), null);
        $dependencyHealthy2 = new HealthcheckStatus($faker->sentence(), null);

        $exceptionDependecy = new Exception($faker->sentence());
        $dependencyBad = new HealthcheckStatus(
            $faker->sentence(),
            $exceptionDependecy,
        );

        return [
            'no dependency' => [
                $version,
                [],
                [
                    'version' => $version,
                    'isHealthy' => true,
                    'dependencies' => [],
                ],
            ],

            '1 dependency and all healthy' => [
                $version,
                [
                    $dependencyHealthy1,
                ],
                [
                    'version' => $version,
                    'isHealthy' => true,
                    'dependencies' => [
                        [
                            'name' => $dependencyHealthy1->name,
                            'isHealthy' => true,
                            'reason' => null,
                        ],
                    ],
                ],
            ],
            '2 dependencies and all healthy' => [
                $version,
                [
                    $dependencyHealthy1,
                    $dependencyHealthy2,
                ],
                [
                    'version' => $version,
                    'isHealthy' => true,
                    'dependencies' => [
                        [
                            'name' => $dependencyHealthy1->name,
                            'isHealthy' => true,
                            'reason' => null,
                        ],
                        [
                            'name' => $dependencyHealthy2->name,
                            'isHealthy' => true,
                            'reason' => null,
                        ],
                    ],
                ],
            ],

            '1 dependency and all bad' => [
                $version,
                [
                    $dependencyBad,
                ],
                [
                    'version' => $version,
                    'isHealthy' => false,
                    'dependencies' => [
                        [
                            'name' => $dependencyBad->name,
                            'isHealthy' => false,
                            'reason' => $exceptionDependecy->getMessage(),
                        ],
                    ],
                ],
            ],
            '2 dependencies and partially bad' => [
                $version,
                [
                    $dependencyHealthy1,
                    $dependencyBad,
                ],
                [
                    'version' => $version,
                    'isHealthy' => false,
                    'dependencies' => [
                        [
                            'name' => $dependencyHealthy1->name,
                            'isHealthy' => true,
                            'reason' => null,
                        ],
                        [
                            'name' => $dependencyBad->name,
                            'isHealthy' => false,
                            'reason' => $exceptionDependecy->getMessage(),
                        ],
                    ],
                ],
            ],
        ];
    }
}
