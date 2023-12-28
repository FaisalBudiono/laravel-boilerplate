<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Query\Enum\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Core\User\UserCoreContract;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\Trait\JSONTrait;

class UserIndexTest extends BaseFeatureTestCase
{
    use JSONTrait;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->count(10)->create();

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input
    ): void {
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
    public function should_show_500_when_generic_error_is_thrown(): void
    {
        // Arrange
        $input = $this->validRequestInput();
        $exceptionMessage = new ExceptionMessageGeneric();

        $mockException = new \Error($this->faker->sentence);


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn (
                        GetAllUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
            $input
        );


        // Assert
        $response->assertInternalServerError();
        $response->assertJsonPath(
            'errors',
            $exceptionMessage->getJsonResponse()->toArray()
        );
    }

    #[Test]
    #[DataProvider('validDataProvider')]
    public function should_show_200_when_successfully_get_user_list(
        array $input,
    ): void {
        // Assert
        $mockedResults = User::query()->paginate();

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedResults) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn (
                        GetAllUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
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
        ];
    }

    protected function getEndpointUrl(): string
    {
        return route('user.index');
    }

    protected function validateRequest(
        GetAllUserPort $argInput,
        array $input
    ): bool {
        try {
            $this->assertSame(
                $input['order_by'] ?? null,
                $argInput->getOrderBy()?->value
            );
            $this->assertSame(
                $input['order_dir'] ?? null,
                $argInput->getOrderDirection()?->value
            );
            $this->assertSame(
                $input['page'] ?? null,
                $argInput->getPage()
            );
            $this->assertSame(
                $input['per_page'] ?? null,
                $argInput->getPerPage()
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
