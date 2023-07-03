<?php

namespace Tests\Unit\Core\Healthcheck\VersionFetcher;

use App\Core\Healthcheck\VersionFetcher\VersionFetcherConfig;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VersionFetcherConfigTest extends TestCase
{
    #[Test]
    public function fullVersion_should_return_full_version()
    {
        // Arrange
        $mockMajor = (string) $this->faker->numberBetween();
        $mockMinor = (string) $this->faker->numberBetween();
        $mockPatch = (string) $this->faker->numberBetween();

        Config::set('version.major', $mockMajor);
        Config::set('version.minor', $mockMinor);
        Config::set('version.patch', $mockPatch);

        $service = $this->makeService();


        // Act
        $result = $service->fullVersion();


        // Assert
        $expectedVersion = "v{$mockMajor}.{$mockMinor}.{$mockPatch}";
        $this->assertSame($expectedVersion, $result);
    }

    #[Test]
    public function major_should_receive_major_version_from_version_config_file()
    {
        // Arrange
        $mockMajor = $this->faker->word;

        Config::set('version.major', $mockMajor);

        $service = $this->makeService();


        // Act
        $result = $service->major();


        // Assert
        $this->assertSame($mockMajor, $result);
    }

    #[Test]
    public function minor_should_receive_major_version_from_version_config_file()
    {
        // Arrange
        $mockMinor = $this->faker->word;

        Config::set('version.minor', $mockMinor);

        $service = $this->makeService();


        // Act
        $result = $service->minor();


        // Assert
        $this->assertSame($mockMinor, $result);
    }

    #[Test]
    public function patch_should_receive_major_version_from_version_config_file()
    {
        // Arrange
        $mockPatch = $this->faker->word;

        Config::set('version.patch', $mockPatch);

        $service = $this->makeService();


        // Act
        $result = $service->patch();


        // Assert
        $this->assertSame($mockPatch, $result);
    }

    protected function makeService(): VersionFetcherConfig
    {
        return new VersionFetcherConfig();
    }
}
