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
use App\Port\Core\Post\UpdatePostPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;

class PostUpdateTest extends BaseFeatureTestCase
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
                $mock->shouldReceive('update')->andReturn(true);
            }
        );
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input
    ): void {
        // Arrange
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockPost->user);


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->mockPost->id),
            $input
        );


        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor($errorMaker, 'errors.meta');
    }

    public static function invalidDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'without title' => [
                'title',
                collect(self::validRequestInput())
                    ->except('title')
                    ->toArray(),
            ],
            'title is null' => [
                'title',
                collect(self::validRequestInput())
                    ->replace([
                        'title' => null,
                    ])->toArray(),
            ],
            'title is empty string' => [
                'title',
                collect(self::validRequestInput())
                    ->replace([
                        'title' => '',
                    ])->toArray(),
            ],
            'title is not string (now contain array)' => [
                'title',
                collect(self::validRequestInput())
                    ->replace([
                        'title' => [$faker->words(3, true)],
                    ])->toArray(),
            ],
            'title should be less than 250 (currently 251)' => [
                'title',
                collect(self::validRequestInput())
                    ->replace([
                        'title' => $faker->regexify('[a-z]{251}'),
                    ])->toArray(),
            ],

            'content is not string (now contain array)' => [
                'content',
                collect(self::validRequestInput())
                    ->replace([
                        'content' => [$faker->words(3, true)],
                    ])->toArray(),
            ],
            'content is not string (now contain number)' => [
                'content',
                collect(self::validRequestInput())
                    ->replace([
                        'content' => [$faker->numberBetween()],
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_401_when_user_has_not_logged_in(): void
    {
        // Act
        $response = $this->putJson(
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
                $mock->shouldReceive('update')->andReturn(false);
            }
        );


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->mockPost->id),
            $this->validRequestInput(),
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

        $input = $this->validRequestInput();

        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockPost->user);

        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($mockException, $input) {
                $mock->shouldReceive('update')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest(
                        $argInput,
                        $input,
                        $this->mockPost,
                        $this->mockPost->user,
                    ))->andThrow($mockException);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        try {
            // Act
            $this->putJson(
                $this->getEndpointUrl($this->mockPost->id),
                $input,
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
    public function should_show_200_when_successfully_update_post_detail(): void
    {
        // Arrange
        $input = $this->validRequestInput();

        MockerAuthenticatedByJWT::make($this)->mockLogin($this->mockPost->user);


        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($input) {
                $mock->shouldReceive('update')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest(
                        $argInput,
                        $input,
                        $this->mockPost,
                        $this->mockPost->user,
                    ))->andReturn($this->mockPost);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->mockPost->id),
            $input,
        );


        // Assert
        $response->assertStatus(Response::HTTP_OK);

        $expectedResponse = json_decode(PostResource::make($this->mockPost)->toJson(), true);
        $response->assertJsonPath('data', $expectedResponse);
    }

    protected function getEndpointUrl(int $postID): string
    {
        return route('post.update', ['postID' => $postID]);
    }

    protected function validateRequest(
        UpdatePostPort $argInput,
        array $input,
        Post $post,
        User $user,
    ): bool {
        try {
            $this->assertEquals($post->id, $argInput->getPost()->id);
            $this->assertEquals($user->id, $argInput->getUserActor()->id);
            $this->assertEquals($input['title'], $argInput->getTitle());
            $this->assertEquals($input['content'], $argInput->getPostContent());

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

    protected static function validRequestInput(): array
    {
        $faker = self::makeFaker();

        return [
            'title' => $faker->words(3, true),
            'content' => $faker->sentences(3, true),
        ];
    }
}
