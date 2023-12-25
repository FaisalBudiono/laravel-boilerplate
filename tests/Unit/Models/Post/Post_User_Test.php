<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Post;

use App\Models\Post\Post;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Post_User_Test extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function should_be_able_to_call_user_relation(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);


        // Assert
        $this->assertTrue($user->is($post->user));
    }
}
