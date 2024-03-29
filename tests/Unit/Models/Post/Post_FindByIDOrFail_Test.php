<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Post;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Exceptions\Models\ModelNotFoundException;
use App\Models\Post\Post;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class Post_FindByIDOrFail_Test extends TestCase
{
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->post = Post::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ]);
    }

    #[Test]
    public function should_successfully_return_post_instance(): void
    {
        // Act
        $post = Post::findByIDOrFail($this->post->id);


        // Assert
        $this->assertTrue($post->is($this->post));
    }

    #[Test]
    public function should_throw_model_not_found_exception_when_id_is_not_found(): void
    {
        try {
            // Act
            Post::findByIDOrFail(1000);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new ModelNotFoundException(new ExceptionMessageStandard(
                'Post ID is not found',
                ExceptionErrorCode::MODEL_NOT_FOUND->value,
            ));
            $this->assertEquals($expectedException, $e);
        }
    }
}
