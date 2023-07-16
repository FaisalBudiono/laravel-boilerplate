<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Core\Query\OrderDirection;
use App\Core\User\Query\UserOrderBy;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\GetAllUserPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\MockerLoggerMessageFactory;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\Helper\ResourceAssertion\User\ResourceAssertionUserList;

class GetAllUserTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected ResourceAssertion $resourceAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->count(10)->create();

        $this->resourceAssertion = new ResourceAssertionUserList;

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
        $this->instance(
            LoggerMessageFactoryContract::class,
            $this->mock(LoggerMessageFactoryContract::class),
        );
        Log::partialMock();
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input
    ) {
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
            'orderBy is not string (now contain array)' => [
                'orderBy',
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => ['kuda'],
                    ])->toArray(),
            ],
            'orderBy is not string (now contain integer)' => [
                'orderBy',
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => 123,
                    ])->toArray(),
            ],
            'orderBy is not valid enum (now contain random string)' => [
                'orderBy',
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => 'kuda',
                    ])->toArray(),
            ],

            'orderDir is not string (now contain array)' => [
                'orderDir',
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => ['kuda'],
                    ])->toArray(),
            ],
            'orderDir is not string (now contain integer)' => [
                'orderDir',
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => 123,
                    ])->toArray(),
            ],
            'orderDir is not valid enum (now contain random string)' => [
                'orderDir',
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => 'kuda',
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

            'perPage is not integer (now contain array)' => [
                'perPage',
                collect(self::validRequestInput())
                    ->replace([
                        'perPage' => [2],
                    ])->toArray(),
            ],
            'perPage is not integer (now contain string)' => [
                'perPage',
                collect(self::validRequestInput())
                    ->replace([
                        'perPage' => 'kambing',
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_500_when_generic_error_is_thrown()
    {
        // Arrange
        $input = $this->validRequestInput();
        $exceptionMessage = new ExceptionMessageGeneric;

        $mockException = new Exception($this->faker->sentence);

        $logInfoMessage = $this->faker->sentence;
        $logErrorMessage = $this->faker->sentence;


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

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Get all user endpoint',
                $input,
                $logInfoMessage,
            )->setHTTPError(
                $mockException,
                $logErrorMessage,
            )->bindInstance();

        Log::shouldReceive('info')
            ->withArgs(function ($argLogMessage) use ($logInfoMessage) {
                try {
                    $this->assertEquals($logInfoMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();
        Log::shouldReceive('error')
            ->withArgs(function ($argLogMessage) use ($logErrorMessage) {
                try {
                    $this->assertEquals($logErrorMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
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
    public function should_show_200_when_successfully_get_user_list(
        array $input,
    ) {
        // Arrange
        $logInfoMessage = $this->faker->sentence;
        $logSuccessMessage = $this->faker->sentence;


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input) {
                $mock->shouldReceive('getAll')
                    ->once()
                    ->withArgs(fn (
                        GetAllUserPort $argInput
                    ) => $this->validateRequest($argInput, $input))
                    ->andReturn(User::query()->paginate());
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Get all user endpoint',
                collect($input)->filter()->toArray(),
                $logInfoMessage,
            )->setHTTPSuccess(
                'Get all user endpoint',
                $logSuccessMessage,
            )->bindInstance();

        Log::shouldReceive('info')
            ->withArgs(function ($argLogMessage) use ($logInfoMessage) {
                try {
                    $this->assertEquals($logInfoMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();
        Log::shouldReceive('info')
            ->withArgs(function ($argLogMessage) use ($logSuccessMessage) {
                try {
                    $this->assertEquals($logSuccessMessage, $argLogMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl() . '?' . http_build_query($input),
            $input
        );


        // Assert
        $response->assertOk();
        $this->resourceAssertion->assertResource($this, $response);
    }

    public static function validDataProvider(): array
    {
        return [
            'complete data' => [
                collect(self::validRequestInput())
                    ->toArray(),
            ],

            'orderBy is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'orderBy' => null
                    ])->toArray(),
            ],
            'without orderBy' => [
                collect(self::validRequestInput())
                    ->except('orderBy')
                    ->toArray(),
            ],

            'orderDir is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'orderDir' => null
                    ])->toArray(),
            ],
            'without orderDir' => [
                collect(self::validRequestInput())
                    ->except('orderDir')
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

            'perPage is null' => [
                collect(self::validRequestInput())
                    ->replace([
                        'perPage' => null
                    ])->toArray(),
            ],
            'without perPage' => [
                collect(self::validRequestInput())
                    ->except('perPage')
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
                $input['orderBy'] ?? null,
                $argInput->getOrderBy()?->value
            );
            $this->assertSame(
                $input['orderDir'] ?? null,
                $argInput->getOrderDirection()?->value
            );
            $this->assertSame(
                $input['page'] ?? null,
                $argInput->getPage()
            );
            $this->assertSame(
                $input['perPage'] ?? null,
                $argInput->getPerPage()
            );
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected static function validRequestInput(): array
    {
        return [
            'orderBy' => UserOrderBy::NAME->value,
            'orderDir' => OrderDirection::ASCENDING->value,
            'page' => 1,
            'perPage' => 15,
        ];
    }
}
