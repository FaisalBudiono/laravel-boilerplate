<?php

namespace Tests\Unit\Core\Healthcheck\HealthcheckCore;

use App\Core\Healthcheck\HealthcheckCore;
use App\Core\Healthcheck\HealthcheckCoreContract;
use App\Core\Healthcheck\Healthchecker\HealthcheckerMysqlContract;
use App\Core\Healthcheck\Healthchecker\HealthcheckerRedisContract;
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
        // Assert
        $this->assertInstanceOf(HealthcheckCoreContract::class, $this->makeService());
    }

    #[Test]
    public function should_return_healthcheck_response()
    {
        // Arrange
        $mockVersion = $this->faker->word;
        $mockedMysqlStatus = new HealthcheckStatus($this->faker->sentence, null);
        $mockedRedisStatus = new HealthcheckStatus($this->faker->sentence, null);


        // Assert
        $mockVersionFetcher = $this->mock(
            VersionFetcher::class,
            function (MockInterface $mock) use ($mockVersion) {
                $mock->shouldReceive('fullVersion')
                    ->once()
                    ->andReturn($mockVersion);
            }
        );
        assert($mockVersionFetcher instanceof VersionFetcher);

        $mockHealthcheckerMysql = $this->mock(
            HealthcheckerMysqlContract::class,
            function (MockInterface $mock) use ($mockedMysqlStatus) {
                $mock->shouldReceive('getStatus')
                    ->once()
                    ->andReturn($mockedMysqlStatus);
            }
        );
        assert($mockHealthcheckerMysql instanceof HealthcheckerMysqlContract);

        $mockHealthcheckerRedis = $this->mock(
            HealthcheckerRedisContract::class,
            function (MockInterface $mock) use ($mockedRedisStatus) {
                $mock->shouldReceive('getStatus')
                    ->once()
                    ->andReturn($mockedRedisStatus);
            }
        );
        assert($mockHealthcheckerRedis instanceof HealthcheckerRedisContract);


        // Act
        $result = $this->makeService(
            $mockVersionFetcher,
            $mockHealthcheckerMysql,
            $mockHealthcheckerRedis,
        )->getHealthiness($this->mockInput);


        // Assert
        $this->assertEquals(
            new HealthcheckResponse(
                $mockVersion,
                $mockedMysqlStatus,
                $mockedRedisStatus,
            ),
            $result,
        );
    }

    protected function makeService(
        ?VersionFetcher $versionFetcher = null,
        ?HealthcheckerMysqlContract $mysqlHealthchecker = null,
        ?HealthcheckerRedisContract $redisHealthchecker = null,
    ): HealthcheckCore {
        if (is_null($versionFetcher)) {
            $versionFetcher = $this->mock(VersionFetcher::class);
        }

        if (is_null($mysqlHealthchecker)) {
            $mysqlHealthchecker = $this->mock(HealthcheckerMysqlContract::class);
        }

        if (is_null($redisHealthchecker)) {
            $redisHealthchecker = $this->mock(HealthcheckerRedisContract::class);
        }

        return new HealthcheckCore(
            $versionFetcher,
            $mysqlHealthchecker,
            $redisHealthchecker,
        );
    }
}
