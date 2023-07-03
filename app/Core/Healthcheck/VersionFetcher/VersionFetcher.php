<?php

namespace App\Core\Healthcheck\VersionFetcher;

interface VersionFetcher
{
    public function fullVersion(): string;
    public function major(): string;
    public function minor(): string;
    public function patch(): string;
}
