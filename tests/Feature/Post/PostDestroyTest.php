<?php

declare(strict_types=1);

namespace Tests\Feature\Post;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Post\Policy\PostPolicyContract;
use App\Core\Post\PostCoreContract;
use App\Exceptions\Http\InternalServerErrorException;
use App\Models\Post\Post;
use App\Port\Core\Post\DeletePostPort;
use Exception;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;

class PostDestroyTest extends BaseFeatureTestCase
{
    protected Post $mockPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockPost = Post::factory()->create([
            'id' => $this->faker->numberBetween(1, 100),
        ]);

        $this->instance(PostCoreContract::class, $this->mock(PostCoreContract::class));

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('delete')->andReturn(true);
            }
        );
    }

    #[Test]
    public function should_show_401_when_user_has_not_logged_in(): void
    {
        // Act
        $response = $this->deleteJson(
            $this->getEndpointUrl($this->mockPost->id),
        );


        // Assert
        $response->assertUnauthorized();
        $response->assertJsonPath('errors.message', 'Authentication is needed');
        $response->assertJsonPath('errors.errorCode', ExceptionErrorCode::REQUIRE_AUTHORIZATION->value);
    }

    #[Test]
    public function should_show_403_when_denied_by_policy(): void
    {
        // Arrange
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockPost->user);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('delete')->andReturn(false);
            }
        );


        // Act
        $response = $this->deleteJson(
            $this->getEndpointUrl($this->mockPost->id),
        );


        // Assert
        $response->assertForbidden();
        $response->assertJsonPath(
            'errors.message',
            'Lack of authorization to access this resource',
        );
        $response->assertJsonPath(
            'errors.errorCode',
            ExceptionErrorCode::LACK_OF_AUTHORIZATION->value,
        );
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockPost->user);

        $mockException = new Exception($this->faker->sentence());


        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($mockException) {
                $mock->shouldReceive('delete')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest(
                        $argInput,
                        $this->mockPost,
                    ))->andThrow($mockException);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        try {
            // Act
            $this->deleteJson(
                $this->getEndpointUrl($this->mockPost->id),
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
            $expectedException = new InternalServerErrorException(
                new ExceptionMessageGeneric(),
                $mockException,
            );
            $this->assertEquals($expectedException, $e);
        }
    }

    #[Test]
    public function should_show_204_when_successfully_delete_post(): void
    {
        // Arrange
        MockerAuthenticatedByJWT::make($this)->mockLogin($this->mockPost->user);


        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('delete')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest(
                        $argInput,
                        $this->mockPost,
                    ))->andReturn($this->mockPost);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        // Act
        $response = $this->deleteJson(
            $this->getEndpointUrl($this->mockPost->id),
        );


        // Assert
        $response->assertNoContent();
    }

    protected function getEndpointUrl(int $postID): string
    {
        return route('post.destroy', ['postID' => $postID]);
    }

    protected function validateRequest(
        DeletePostPort $argInput,
        Post $post,
    ): bool {
        try {
            $this->assertEquals($post->id, $argInput->getPost()->id);

            return true;
        } catch (ExpectationFailedException $e) {
            dump(
                $e->toString(),
                $e->getComparisonFailure()
            );
            return false;
        } catch (\Throwable $e) {
            dump($e);
            return false;
        }
    }
}
