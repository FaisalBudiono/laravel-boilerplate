<?php

declare(strict_types=1);

namespace Tests\Unit\Core\Post\PostCore;

use App\Models\User\User;
use App\Port\Core\Post\CreatePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PostCore_Create_Test extends PostCoreBaseTestCase
{
    protected CreatePostPort|MockInterface $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRequest = $this->mock(CreatePostPort::class);
    }

    #[Test]
    #[DataProvider('inputDataProvider')]
    public function should_successfully_create_post(
        string $title,
        ?string $content,
    ): void {
        // Arrange
        $mockUsers = User::factory()->count($this->faker()->numberBetween(2, 10))->create();
        $user = $this->faker()->randomElement($mockUsers);
        assert($user instanceof User);

        $this->mockRequest->shouldReceive('getUserActor')->once()->andreturn($user);
        $this->mockRequest->shouldReceive('getTitle')->once()->andreturn($title);
        $this->mockRequest->shouldReceive('getPostContent')->once()->andreturn($content);


        // Act
        $result = $this->makeService()->create($this->mockRequest);


        // Assert
        $this->assertDatabaseCount('posts', 1);

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
