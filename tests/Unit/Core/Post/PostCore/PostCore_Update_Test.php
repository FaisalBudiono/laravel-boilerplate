<?php

namespace Tests\Unit\Core\Post\PostCore;

use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\UpdatePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Update_Test extends PostCoreBaseTestCase
{
    protected UpdatePostPort|MockInterface $mockRequest;

    protected Post $mockPost;

    protected function setUp(): void
    {
        parent::setUp();

        $mockPosts = Post::factory()->count(
            $this->faker->numberBetween(2, 10),
        )->create();
        $this->mockPost = $this->faker->randomElement($mockPosts);

        $this->mockRequest = $this->mock(UpdatePostPort::class);
    }

    #[Test]
    #[DataProvider('inputDataProvider')]
    public function should_successfully_update_post(
        string $title,
        ?string $content,
    ): void {
        // Arrange
        $user = $this->faker()->randomElement(User::all());
        assert($user instanceof User);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andreturn($user);
        $this->mockRequest->shouldReceive('getPost')->once()->andreturn($this->mockPost);
        $this->mockRequest->shouldReceive('getTitle')->once()->andreturn($title);
        $this->mockRequest->shouldReceive('getContent')->once()->andreturn($content);


        // Act
        $result = $this->makeService()->update($this->mockRequest);


        // Assert
        $this->assertSame($this->mockPost->id, $result->id);
        $this->assertSame($title, $result->title);
        $this->assertSame($content, $result->content);
        $this->assertSame($user->id, $result->user_id);

        $this->assertLoadedRelationships($result);
    }

    public static function inputDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'complete data' => [
                $faker->word(),
                $faker->sentence(),
            ],
            'content is null' => [
                $faker->word(),
                null,
            ],
        ];
    }
}
