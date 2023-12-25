<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Core\Post\PostCoreContract;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Interface_Test extends PostCoreBaseTestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(PostCoreContract::class, $this->makeService());
    }
}
