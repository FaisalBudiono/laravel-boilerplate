<?php

declare(strict_types=1);

namespace Tests\Feature\Post;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Post\Policy\PostPolicyContract;
use App\Core\Post\PostCoreContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Exceptions\Http\AbstractHttpException;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Resources\Post\PostResource;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\GetSinglePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;

class PostShowTest extends BaseFeatureTestCase
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
                $mock->shouldReceive('see')->andReturn(true);
            }
        );
    }

    #[Test]
    public function should_show_401_when_user_has_not_logged_in(): void
    {
        // Act
        $response = $this->getJson(
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
        $notOwnerUser = User::factory()->create();
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($notOwnerUser);

        $this->mock(
            PostPolicyContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('see')->andReturn(false);
            }
        );


        // Act
        $response = $this->getJson(
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
    #[DataProvider('exceptionDataProvider')]
    public function should_show_error_code_when_thrown_by_core(
        \Throwable $mockException,
        AbstractHttpException $expectedException,
    ): void {
        // Arrange
        $this->withoutExceptionHandling();

        $userActor = User::factory()->create();
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($userActor);

        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($mockException, $userActor) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest(
                        $argInput,
                        $this->mockPost,
                        $userActor,
                    ))->andThrow($mockException);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        try {
            // Act
            $this->getJson(
                $this->getEndpointUrl($this->mockPost->id),
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->assertEquals($expectedException, $e);
        }
    }

    public static function exceptionDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'generic exception - 500' => [
                $e = new \Error($faker->sentence()),
                new InternalServerErrorException(
                    new ExceptionMessageGeneric(),
                    $e,
                ),
            ],
            'permission exception - 403' => [
                $e = new InsufficientPermissionException(new ExceptionMessageStandard(
                    $faker->sentence(),
                    $faker->sentence(),
                )),
                new ForbiddenException(
                    $e->exceptionMessage,
                    $e,
                ),
            ],
        ];
    }

    #[Test]
    public function should_show_200_when_successfully_get_post_detail(): void
    {
        // Arrange
        $userActor = User::factory()->create();
        MockerAuthenticatedByJWT::make($this)->mockLogin($userActor);

        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($userActor) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest(
                        $argInput,
                        $this->mockPost,
                        $userActor,
                    ))->andReturn($this->mockPost);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl($this->mockPost->id),
        );


        // Assert
        $response->assertStatus(Response::HTTP_OK);

        $expectedResponse = json_decode(PostResource::make($this->mockPost)->toJson(), true);
        $response->assertJsonPath('data', $expectedResponse);
    }

    protected function getEndpointUrl(int $postID): string
    {
        return route('post.show', ['postID' => $postID]);
    }

    protected function validateRequest(
        GetSinglePostPort $argInput,
        Post $post,
        User $user,
    ): bool {
        try {
            $this->assertTrue(
                $post->is($argInput->getPost()),
                'Post is not the same',
            );
            $this->assertTrue(
                $user->is($argInput->getUserActor()),
                'User is not the same',
            );
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
