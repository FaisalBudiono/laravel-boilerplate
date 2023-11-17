<?php

namespace Tests\Feature\Post;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Post\PostCoreContract;
use App\Http\Resources\Post\PostResource;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\CreatePostPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;

class PostStoreTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
            ->mockLogin(User::factory()->create());


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
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
        // Arrange
        $input = $this->validRequestInput();


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input
        );


        // Assert
        $response->assertUnauthorized();
        $response->assertJsonPath('errors.message', 'Authentication is needed');
        $response->assertJsonPath('errors.errorCode', ExceptionErrorCode::REQUIRE_AUTHORIZATION->value);
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown(): void
    {
        // Arrange
        $input = $this->validRequestInput();

        $mockedUser = User::factory()->create();
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($mockedUser);

        $exceptionMessage = new ExceptionMessageGeneric;
        $mockException = new Exception($this->faker->sentence());


        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException, $mockedUser) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest($argInput, $input, $mockedUser))
                    ->andThrow($mockException);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonPath(
            'errors',
            $exceptionMessage->getJsonResponse()->toArray()
        );
    }

    #[Test]
    #[DataProvider('validDataProvider')]
    public function should_show_201_when_successfully_create_post(
        array $input,
    ): void {
        // Arrange
        $mockedPost = Post::factory()->create();
        assert($mockedPost instanceof Post);

        MockerAuthenticatedByJWT::make($this)->mockLogin($mockedPost->user);


        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedPost) {
                $mock->shouldReceive('create')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest($argInput, $input, $mockedPost->user))
                    ->andReturn($mockedPost);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        // Act
        $response = $this->postJson(
            $this->getEndpointUrl(),
            $input
        );


        // Assert
        $response->assertStatus(Response::HTTP_CREATED);

        $expectedResponse = json_decode(PostResource::make($mockedPost)->toJson(), true);
        $response->assertJsonPath('data', $expectedResponse);
    }

    public static function validDataProvider(): array
    {
        return [
            'complete data' => [
                self::validRequestInput(),
            ],

            'without content' => [
                collect(self::validRequestInput())
                    ->except('content')
                    ->toArray(),
            ],
            'content is null' => [
                collect(self::validRequestInput())
                    ->replace(['content' => null])
                    ->toArray(),
            ],
        ];
    }

    protected function getEndpointUrl(): string
    {
        return route('post.store');
    }

    protected function validateRequest(
        CreatePostPort $argInput,
        array $input,
        User $user,
    ): bool {
        try {
            $this->assertSame($input['title'], $argInput->getTitle());

            $this->assertEquals($input['content'] ?? null, $argInput->getPostContent());
            $this->assertTrue($user->is($argInput->getUserActor()));
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
