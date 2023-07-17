<?php

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;
use Tests\TestCase;

abstract class CacherLaravelBaseTestCase extends TestCase
{
    protected function getPrefixName(): string
    {
        return config('jwt.refresh.prefix');
    }

    protected function makeService(): CacherLaravel
    {
        return new CacherLaravel;
    }
}
