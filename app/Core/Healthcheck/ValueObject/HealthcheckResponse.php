<?php

namespace App\Core\Healthcheck\ValueObject;

use Illuminate\Contracts\Support\Arrayable;

readonly class HealthcheckResponse implements Arrayable
{
    /**
     * @var array<HealthcheckStatus>
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

    protected function mapDependencyStatus(
        HealthcheckStatus $dependency,
    ): array {
        return [
            'name' => $dependency->name,
            'isHealthy' => $this->isDependencyHealthy($dependency),
            'reason' => optional($dependency->error)->getMessage(),
        ];
    }
}
