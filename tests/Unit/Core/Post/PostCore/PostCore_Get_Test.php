<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Models\Post\Post;
use App\Port\Core\Post\GetSinglePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Get_Test extends PostCoreBaseTestCase
{
    protected GetSinglePostPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(GetSinglePostPort::class);
    }

    #[Test]
    public function should_return_post_with_relationship_loaded(): void
    {
        // Arrange
        $mockPost = $this->faker->randomElement(Post::factory()->count(10)->create());
        assert($mockPost instanceof Post);

        $this->mockRequest->shouldReceive('getPost')->once()->andReturn($mockPost);


        // Act
        $result = $this->makeService()->get($this->mockRequest);


        // Assert
        $this->assertSame($mockPost->id, $result->id);
        $this->assertLoadedRelationships($result);
    }
}
