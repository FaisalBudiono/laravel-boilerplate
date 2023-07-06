<?php

namespace Tests\Unit\Core\Healthcheck\HealthcheckCore;

use App\Core\Healthcheck\HealthcheckCore;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\Healthchecker\HealthcheckerMysqlContract;
use App\Core\Healthcheck\ValueObject\HealthcheckResponse;
use App\Core\Healthcheck\ValueObject\HealthcheckStatus;
use App\Core\Healthcheck\VersionFetcher\VersionFetcher;
use App\Port\Core\Healthcheck\GetHealthcheckPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthcheckCore_GetHealthiness_Test extends TestCase
{
    protected GetHealthcheckPort $mockInput;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockInput = $this->mock(GetHealthcheckPort::class);
    }

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

        $mockedMysqlStatus = new HealthcheckStatus('mysql', null);
        /** @var HealthcheckerMysqlContract */
        $mockHealthcheckerMysql = $this->mock(
            HealthcheckerMysqlContract::class,
            function (MockInterface $mock) use ($mockedMysqlStatus) {
                $mock->shouldReceive('getStatus')
                    ->andReturn($mockedMysqlStatus);
            }
        );

        $service = $this->makeService($mockVersionFetcher, $mockHealthcheckerMysql);


        // Act
        $result = $service->getHealthiness($this->mockInput);


        // Assert
        $this->assertEquals(
            new HealthcheckResponse($mockVersion, $mockedMysqlStatus),
            $result,
        );
    }

    protected function makeService(
        ?VersionFetcher $versionFetcher = null,
        ?HealthcheckerMysqlContract $mysqlHealthchecker = null,
    ): HealthcheckCore {
        if (is_null($versionFetcher)) {
            $versionFetcher = $this->mock(VersionFetcher::class);
        }

        if (is_null($mysqlHealthchecker)) {
            $mysqlHealthchecker = $this->mock(HealthcheckerMysqlContract::class);
        }

        return new HealthcheckCore(
            $versionFetcher,
            $mysqlHealthchecker,
        );
    }
}
