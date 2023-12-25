<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Auth\JWT\Refresh\Cacher\CacherLaravel;

use App\Core\Auth\JWT\Refresh\Cacher\Cacher;
use PHPUnit\Framework\Attributes\Test;

class CacherLaravel_Interface_Test extends CacherLaravelBaseTestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(Cacher::class, $this->makeService());
    }
}
