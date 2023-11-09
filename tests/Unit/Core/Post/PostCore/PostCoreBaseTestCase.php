<?php

namespace Tests\Unit\Core\Post\PostCore;

use App\Core\Post\PostCore;
use App\Models\Post\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PostCoreBaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeService(): PostCore
    {
        return new PostCore;
    }

    protected function assertLoadedRelationships(Post $model): void
    {
        $this->expectationLoadedRelationships()->each(function (string $relationship) use ($model) {
            $this->assertTrue(
                $model->relationLoaded($relationship),
                "{$relationship} not loaded",
            );
        });
    }

    protected function expectationLoadedRelationships(): Collection
    {
        return collect([
            'user',
        ]);
    }
}
