<?php

declare(strict_types=1);

namespace Tests\Unit\Models\User;

use App\Models\Post\Post;
use App\Models\User\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class User_Posts_Test extends TestCase
{
    #[Test]
    public function should_be_able_to_call_posts_relationship(): void
    {
        // Arrange
        $user = User::factory()->create();

        $totalUserPost = $this->faker->numberBetween(1, 10);
        Post::factory()->count($totalUserPost)->create([
            'user_id' => $user->id,
        ]);

        $totalOtherPost = $this->faker->numberBetween(1, 10);
        Post::factory()->count($totalOtherPost)->create();


        // Assert
        $this->assertCount($totalUserPost, $user->posts);
    }
}
