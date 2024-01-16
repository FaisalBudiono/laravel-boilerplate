<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Policy\UserPolicyContract;
use App\Core\User\Query\UserOrderBy;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Exceptions\Http\AbstractHttpException;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;
use Tests\Helper\Trait\EmptyStringToNullTrait;
use Tests\Helper\Trait\JSONTrait;

class UserIndexTest extends BaseFeatureTestCase
{
    use EmptyStringToNullTrait;
    use JSONTrait;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->count(10)->create();

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('seeAll')->andReturn(true);
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
        MockerAuthenticatedByJWT::make($this)->mockLogin(User::factory()->create());


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
        return [
            'order_by is not string (now contain array)' => [
                'order_by',
                collect(self::validRequestInput())
                    ->replace([
                        'order_by' => ['kuda'],
                    ])->toArray(),
            ],
            'order_by is not string (now contain integer)' => [
                'order_by',
                collect(self::validRequestInput())
                    ->replace([
                        'order_by' => 123,
                    ])->toArray(),
            ],
            'order_by is not valid enum (now contain random string)' => [
                'order_by',
                collect(self::validRequestInput())
                    ->replace([
                        'order_by' => 'kuda',
                    ])->toArray(),
            ],

            'order_dir is not string (now contain array)' => [
                'order_dir',
                collect(self::validRequestInput())
                    ->replace([
                        'order_dir' => ['kuda'],
                    ])->toArray(),
            ],
            'order_dir is not string (now contain integer)' => [
                'order_dir',
                collect(self::validRequestInput())
                    ->replace([
                        'order_dir' => 123,
                    ])->toArray(),
            ],
            'order_dir is not valid enum (now contain random string)' => [
                'order_dir',
                collect(self::validRequestInput())
                    ->replace([
                        'order_dir' => 'kuda',
                    ])->toArray(),
            ],

            'page is not integer (now contain array)' => [
                'page',
                collect(self::validRequestInput())
                    ->replace([
                        'page' => [2],
                    ])->toArray(),
            ],
            'page is not integer (now contain string)' => [
                'page',
                collect(self::validRequestInput())
                    ->replace([
                        'page' => 'kambing',
                    ])->toArray(),
            ],

            'per_page is not integer (now contain array)' => [
                'per_page',
                collect(self::validRequestInput())
                    ->replace([
                        'per_page' => [2],
                    ])->toArray(),
            ],
            'per_page is not integer (now contain string)' => [
                'per_page',
                collect(self::validRequestInput())
                    ->replace([
                        'per_page' => 'kambing',
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_401_when_not_logged_in(): void
    {
        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
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
        MockerAuthenticatedByJWT::make($this)->mockLogin(
            User::factory()->create()->fresh(),
        );

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('seeAll')->andReturn(false);
            }
        );



        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
        );


        // Assert
        $response->assertForbidden();
        $response->assertJsonPath('errors.message', 'Lack of authorization to access this resource');
        $response->assertJsonPath('errors.errorCode', ExceptionErrorCode::LACK_OF_AUTHORIZATION->value);
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

        MockerAuthenticatedByJWT::make($this)->mockLogin(
            $userActor = User::factory()->create(),
        );


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException, $userActor) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn (
                        GetAllUserPort $argInput
                    ) => $this->validateRequest($argInput, $input, $userActor))
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        try {
            // Act
            $this->getJson(
                $this->getEndpointUrl() . '?' . http_build_query($input),
                $input
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Assert
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
    #[DataProvider('validDataProvider')]
    public function should_show_200_when_successfully_get_user_list(
        array $input,
    ): void {
        // Assert
        $mockedResults = User::query()->paginate();

        MockerAuthenticatedByJWT::make($this)->mockLogin(
            $userActor = User::factory()->create(),
        );

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedResults, $userActor) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn (
                        GetAllUserPort $argInput
                    ) => $this->validateRequest($argInput, $input, $userActor))
                    ->andReturn($mockedResults);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
            $input
        );


        // Assert
        $response->assertOk();
        $response->assertJsonPath(
            'data',
            $this->jsonToArray(UserResource::collection($mockedResults)->toJson()),
        );
    }

    public static function validDataProvider(): array
    {
        return [
            'complete data' => [
                collect(self::validRequestInput())
                    ->toArray(),
            ],

            'order_by is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'order_by' => null
                    ])->toArray(),
            ],
            'without order_by' => [
                collect(self::validRequestInput())
                    ->except('order_by')
                    ->toArray(),
            ],
            'order_by is empty string' => [
                collect(self::validRequestInput())
                    ->replace(['order_by' => ''])
                    ->toArray(),
            ],

            'order_dir is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'order_dir' => null
                    ])->toArray(),
            ],
            'without order_dir' => [
                collect(self::validRequestInput())
                    ->except('order_dir')
                    ->toArray(),
            ],
            'order_dir is empty string' => [
                collect(self::validRequestInput())
                    ->replace(['order_dir' => ''])
                    ->toArray(),
            ],

            'page is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'page' => null
                    ])->toArray(),
            ],
            'without page' => [
                collect(self::validRequestInput())
                    ->except('page')
                    ->toArray(),
            ],
            'page is empty string' => [
                collect(self::validRequestInput())
                    ->replace(['page' => ''])
                    ->toArray(),
            ],

            'per_page is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'per_page' => null
                    ])->toArray(),
            ],
            'without per_page' => [
                collect(self::validRequestInput())
                    ->except('per_page')
                    ->toArray(),
            ],
            'per_page is empty string' => [
                collect(self::validRequestInput())
                    ->replace(['per_page' => ''])
                    ->toArray(),
            ],
        ];
    }

    protected function getEndpointUrl(): string
    {
        return route('user.index');
    }

    protected function validateRequest(
        GetAllUserPort $argInput,
        array $input,
        User $loggInUser,
    ): bool {
        try {
            $this->assertSame(
                $this->emptyStringToNull($input['order_by'] ?? null),
                $argInput->getOrderBy()?->value
            );
            $this->assertSame(
                $this->emptyStringToNull($input['order_dir'] ?? null),
                $argInput->getOrderDirection()?->value
            );
            $this->assertSame(
                $this->emptyStringToNull($input['page'] ?? null),
                $argInput->getPage()
            );
            $this->assertSame(
                $this->emptyStringToNull($input['per_page'] ?? null),
                $argInput->getPerPage()
            );
            $this->assertTrue(
                $argInput->getUserActor()->is($loggInUser),
                'User actor is not the same',
            );
            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }

    protected static function validRequestInput(): array
    {
        return [
            'order_by' => UserOrderBy::NAME->value,
            'order_dir' => OrderDirection::ASCENDING->value,
            'page' => 1,
            'per_page' => 15,
        ];
    }
}
