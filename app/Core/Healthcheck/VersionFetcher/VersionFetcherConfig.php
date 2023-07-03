<?php

namespace App\Core\Healthcheck\VersionFetcher;

class VersionFetcherConfig implements VersionFetcher
{
    public function fullVersion(): string
    {
        return "v{$this->major()}.{$this->minor()}.{$this->patch()}";
    }

    public function major(): string
    {
        return config('version.major', '0');
    }

    public function minor(): string
    {
        return config('version.minor', '0');
    }

    public function patch(): string
    {
        return config('version.patch', '0');
    }
}
