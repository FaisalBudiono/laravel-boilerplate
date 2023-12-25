<?php

declare(strict_types=1);

namespace Tests\Feature\Post;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Post\PostCoreContract;
use App\Http\Resources\Post\PostResource;
use App\Models\Post\Post;
use App\Models\User\User;
use App\Port\Core\Post\GetAllPostPort;
use Exception;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;

class PostIndexTest extends BaseFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'id' => self::userDummyID(),
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
            ->mockLogin(User::factory()->create());


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
        );


        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor($errorMaker, 'errors.meta');
    }

    public static function invalidDataProvider(): array
    {
        $faker = self::makeFaker();

        return [
            'user_id is not a number (now contain string)' => [
                'user_id',
                collect(self::validRequestInput())
                    ->replace([
                        'user_id' => $faker->words(3, true),
                    ])->toArray(),
            ],
            'user_id is not a number (now contain array)' => [
                'user_id',
                collect(self::validRequestInput())
                    ->replace([
                        'user_id' => [$faker->numberBetween()],
                    ])->toArray(),
            ],

            'page is not a number (now contain string)' => [
                'page',
                collect(self::validRequestInput())
                    ->replace([
                        'page' => $faker->words(3, true),
                    ])->toArray(),
            ],
            'page is not a number (now contain array)' => [
                'page',
                collect(self::validRequestInput())
                    ->replace([
                        'page' => [$faker->numberBetween()],
                    ])->toArray(),
            ],

            'per_page is not a number (now contain string)' => [
                'per_page',
                collect(self::validRequestInput())
                    ->replace([
                        'per_page' => $faker->words(3, true),
                    ])->toArray(),
            ],
            'per_page is not a number (now contain array)' => [
                'per_page',
                collect(self::validRequestInput())
                    ->replace([
                        'per_page' => [$faker->numberBetween()],
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_401_when_user_has_not_logged_in(): void
    {
        // Act
        $response = $this->getJson(
            $this->getEndpointUrl()
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

        $mockedLoggedInUser = User::factory()->create();
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($mockedLoggedInUser);

        $exceptionMessage = new ExceptionMessageGeneric();
        $mockException = new Exception($this->faker->sentence());


        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($mockException, $mockedLoggedInUser, $input) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest(
                        $argInput,
                        $input,
                        $mockedLoggedInUser,
                    ))->andThrow($mockException);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
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
    public function should_show_200_when_successfully_get_all_posts(
        array $input,
    ): void {
        // Arrange
        Post::factory()->count(10)->create();
        $mockedPosts = Post::query()->paginate();

        $mockedLoggedInUser = User::factory()->create();
        MockerAuthenticatedByJWT::make($this)->mockLogin($mockedLoggedInUser);


        // Assert
        $mockCore = $this->mock(
            PostCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedLoggedInUser, $mockedPosts) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn ($argInput) => $this->validateRequest($argInput, $input, $mockedLoggedInUser))
                    ->andReturn($mockedPosts);
            }
        );
        $this->instance(PostCoreContract::class, $mockCore);


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
        );


        // Assert
        $response->assertStatus(Response::HTTP_OK);

        $expectedResponse = json_decode(PostResource::collection($mockedPosts)->toJson(), true);
        $response->assertJsonPath('data', $expectedResponse);
    }

    public static function validDataProvider(): array
    {
        return [
            'complete data' => [
                self::validRequestInput(),
            ],

            'without per_page' => [
                collect(self::validRequestInput())
                    ->except('per_page')
                    ->toArray(),
            ],
            'per_page is null' => [
                collect(self::validRequestInput())
                    ->replace(['per_page' => null])
                    ->toArray(),
            ],

            'without page' => [
                collect(self::validRequestInput())
                    ->except('page')
                    ->toArray(),
            ],
            'page is null' => [
                collect(self::validRequestInput())
                    ->replace(['page' => null])
                    ->toArray(),
            ],

            'without user_id' => [
                collect(self::validRequestInput())
                    ->except('user_id')
                    ->toArray(),
            ],
            'user_id is null' => [
                collect(self::validRequestInput())
                    ->replace(['user_id' => null])
                    ->toArray(),
            ],
        ];
    }

    protected function getEndpointUrl(): string
    {
        return route('post.index');
    }

    protected function validateRequest(
        GetAllPostPort $argInput,
        array $input,
        User $loggedInUser,
    ): bool {
        try {
            $this->assertEquals(
                $input['user_id'] ?? null,
                $argInput->getUserFilter()?->id,
            );
            $this->assertEquals(
                $input['page'] ?? 1,
                $argInput->getPage(),
            );
            $this->assertEquals(
                $input['per_page'] ?? null,
                $argInput->getPerPage(),
            );
            $this->assertEquals(
                $loggedInUser->id,
                $argInput->getUserActor()->id,
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


    protected static function userDummyID(): int
    {
        return 777;
    }

    protected static function validRequestInput(): array
    {
        $faker = self::makeFaker();

        return [
            'user_id' => self::userDummyID(),
            'page' => $faker->numberBetween(),
            'per_page' => $faker->numberBetween(),
        ];
    }
}
