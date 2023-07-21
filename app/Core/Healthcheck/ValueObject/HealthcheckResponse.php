<?php

namespace App\Core\Healthcheck\ValueObject;

use Illuminate\Contracts\Support\Arrayable;

readonly class HealthcheckResponse implements Arrayable
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

    public function toArray()
    {
        return [
            'version' => $this->version,
            'isHealthy' => $this->isOverallHealthy(),
            'dependencies' => $this->mapDependencies(),
        ];
    }

    public function toArrayDetail()
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
            fn (HealthcheckStatus $dependency) => $this->isDependencyHealthy($dependency)
        );
    }

    protected function mapDependencies(): array
    {
        return collect($this->dependencies)->map(
            fn (HealthcheckStatus $dependency) => $this->mapDependencyStatus($dependency)
        )->toArray();
    }

    protected function mapDetailDependencies(): array
    {
        return collect($this->dependencies)->map(
            fn (HealthcheckStatus $dependency) => $this->mapDetailDependencyStatus($dependency)
        )->toArray();
    }

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
