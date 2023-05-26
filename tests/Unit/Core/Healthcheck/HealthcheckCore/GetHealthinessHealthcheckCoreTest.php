<?php

namespace Tests\Unit\Core\Healthcheck\HealthcheckCore;

use App\Core\Healthcheck\HealthcheckCore;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetHealthinessHealthcheckCoreTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface()
    {
        // Arrange
        $service = $this->makeService();


        // Assert
        $this->assertInstanceOf(HealthcheckCoreContract::class, $service);
    }

    #[Test]
    public function should_return_healthcheck_response()
    {
        // Arrange
        $mockVersion = $this->faker->word;

        /** @var VersionFetcher */
        $mockVersionFetcher = $this->mock(
            VersionFetcher::class,
            function (MockInterface $mock) use ($mockVersion) {
                $mock->shouldReceive('fullVersion')
                    ->andReturn($mockVersion);
            }
        );

        $service = $this->makeService($mockVersionFetcher);


        // Act
        $result = $service->getHealthiness();


        // Assert
        $this->assertEquals(
            new HealthcheckResponse($mockVersion),
            $result,
        );
    }

    protected function makeService(
        ?VersionFetcher $versionFetcher = null
    ): HealthcheckCore {
        if (is_null($versionFetcher)) {
            $versionFetcher = $this->mock(VersionFetcher::class);
        }

        return new HealthcheckCore($versionFetcher);
    }
}
