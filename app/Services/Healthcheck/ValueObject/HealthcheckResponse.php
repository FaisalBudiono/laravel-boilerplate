<?php

declare(strict_types=1);

namespace App\Services\Healthcheck\ValueObject;

readonly class HealthcheckResponse
{
    /**
     * @var array<int,HealthcheckStatus>
     */
    public array $dependencies;

    public function __construct(
        public string $version,
        HealthcheckStatus ...$dependencies,
    ) {
        $this->dependencies = $dependencies;
    }

    public function isHealthy(): bool
    {
        return $this->isOverallHealthy();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'isHealthy' => $this->isOverallHealthy(),
            'dependencies' => $this->mapDependencies(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArrayDetail(): array
    {
        return [
            'version' => $this->version,
            'isHealthy' => $this->isOverallHealthy(),
            'dependencies' => $this->mapDetailDependencies(),
        ];
    }

    protected function isDependencyHealthy(HealthcheckStatus $dependency): bool
    {
        return is_null($dependency->error);
    }

    protected function isOverallHealthy(): bool
    {
        return collect($this->dependencies)->every(
            fn (HealthcheckStatus $dependency) => $this->isDependencyHealthy($dependency),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function mapDependencies(): array
    {
        return collect($this->dependencies)->map(
            fn (HealthcheckStatus $dependency) => $this->mapDependencyStatus($dependency),
        )->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function mapDetailDependencies(): array
    {
        return collect($this->dependencies)->map(
            fn (HealthcheckStatus $dependency) => $this->mapDetailDependencyStatus($dependency),
        )->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapDetailDependencyStatus(
        HealthcheckStatus $dependency,
    ): array {
        return [
            'name' => $dependency->name,
            'isHealthy' => $this->isDependencyHealthy($dependency),
            'reason' => $dependency->error?->getMessage(),
            'trace' => $dependency->error?->getTrace(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapDependencyStatus(
        HealthcheckStatus $dependency,
    ): array {
        return [
            'name' => $dependency->name,
            'isHealthy' => $this->isDependencyHealthy($dependency),
            'reason' => $dependency->error?->getMessage(),
        ];
    }
}
