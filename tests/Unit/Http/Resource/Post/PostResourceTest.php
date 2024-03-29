<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resource\Post;

use App\Core\Date\DatetimeFormat;
use App\Http\Resources\Post\PostResource;
use App\Http\Resources\User\UserResource;
use App\Models\Post\Post;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    #[Test]
    #[DataProvider('dateDataProvider')]
    public function should_return_right_arrayable_format(
        ?Carbon $mockDate
    ): void {
        // Arrange
        $post = Post::factory()->create([
            'created_at' => $mockDate,
            'updated_at' => $mockDate,
        ]);
        assert($post instanceof Post);


        // Act
        $result = PostResource::make($post)->toJson();


        // Assert
        $this->assertJsonStringEqualsJsonString(json_encode([
            ...$this->makeDefaultResponse($post),
        ]), $result);
    }

    public static function dateDataProvider(): array
    {
        return [
            'filled date' => [
                now(),
            ],
            'date is null' => [
                null,
            ],
        ];
    }

    #[Test]
    public function should_return_array_with_user_relationship(): void
    {
        // Arrange
        $post = Post::factory()->create()->fresh(['user']);
        assert($post instanceof Post);


        // Act
        $result = PostResource::make($post)->toJson();


        // Assert
        $this->assertJsonStringEqualsJsonString(json_encode([
            ...$this->makeDefaultResponse($post),
            'user' => json_decode(UserResource::make($post->user)->toJson(), true),
        ]), $result);
    }

    protected function makeDefaultResponse(Post $post): array
    {
        return [
            'id' => (string) $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'createdAt' => $post->created_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
            'updatedAt' => $post->updated_at?->format(DatetimeFormat::ISO_WITH_MILLIS->value),
        ];
    }
}
