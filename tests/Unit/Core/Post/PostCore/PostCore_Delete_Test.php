<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Models\Post\Post;
use App\Port\Core\Post\DeletePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Delete_Test extends PostCoreBaseTestCase
{
    protected DeletePostPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(DeletePostPort::class);
    }

    #[Test]
    public function should_soft_delete_post(): void
    {
        // Arrange
        $totalPost = 10;
        $mockPost = $this->faker->randomElement(
            Post::factory()->count($totalPost)->create()
        );
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getPost')->once()->andReturn($mockPost);


        // Act
        $this->makeService()->delete($this->mockRequest);


        // Assert
        $this->assertDatabaseCount('posts', $totalPost);
        $this->assertSoftDeleted('posts', [
            'id' => $mockPost->id,
        ]);
    }

    #[Test]
    public function should_be_able_to_soft_delete_post_multiple_times(): void
    {
        // Arrange
        $totalPost = 10;
        $mockPost = $this->faker->randomElement(
            Post::factory()->count($totalPost)->create()
        );
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getPost')->atLeast()->once()->andReturn($mockPost);


        // Act
        $this->makeService()->delete($this->mockRequest);
        $this->makeService()->delete($this->mockRequest);
        $this->makeService()->delete($this->mockRequest);


        // Assert
        $this->assertDatabaseCount('posts', $totalPost);
        $this->assertSoftDeleted('posts', [
            'id' => $mockPost->id,
        ]);
    }
}
