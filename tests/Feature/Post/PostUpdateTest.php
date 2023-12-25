<?php

declare(strict_types=1);

namespace Tests\Feature\Post;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Post\PostCoreContract;
use App\Http\Resources\Post\PostResource;
use App\Models\Permission\Enum\RoleName;
use App\Models\Permission\Role;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\UpdatePostPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;

class PostUpdateTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected Post $mockPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockPost = Post::factory()->create([
            'id' => $this->faker->numberBetween(1, 100),
        ]);

        $this->instance(PostCoreContract::class, $this->mock(PostCoreContract::class));
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
    public function should_show_403_when_user_neither_the_owner_or_admin(): void
    {
        // Arrange
        $notOwnerUser = User::factory()->create();
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($notOwnerUser);


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
    public function should_show_500_when_generic_error_is_thrown(): void
    {
        // Arrange
        $input = $this->validRequestInput();

        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockPost->user);

        $exceptionMessage = new ExceptionMessageGeneric();
        $mockException = new Exception($this->faker->sentence());


        // Assert
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


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->mockPost->id),
            $input,
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonPath(
            'errors',
            $exceptionMessage->getJsonResponse()->toArray()
        );
    }

    #[Test]
    public function should_show_200_when_owner_successfully_update_post_detail(): void
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

    #[Test]
    public function should_show_200_when_admin_successfully_update_post_detail(): void
    {
        // Arrange
        $input = $this->validRequestInput();

        $adminRole = Role::create([
            'name' => RoleName::ADMIN,
        ]);

        $adminNotOwner = User::factory()->create();
        $adminNotOwner->assignRole($adminRole);

        MockerAuthenticatedByJWT::make($this)->mockLogin($adminNotOwner);


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
