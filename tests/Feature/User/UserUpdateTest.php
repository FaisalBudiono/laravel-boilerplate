<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\User\Policy\UserPolicyContract;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Exceptions\Core\User\UserEmailDuplicatedException;
use App\Exceptions\Http\AbstractHttpException;
use App\Exceptions\Http\ConflictException;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Port\Core\User\UpdateUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;
use Tests\Helper\Trait\JSONTrait;

class UserUpdateTest extends BaseFeatureTestCase
{
    use JSONTrait;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ]);

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('update')->andReturn(true);
            }
        );
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_show_422_when_input_is_invalid(
        string $errorMaker,
        array $input,
    ): void {
        // Arrange
        MockerAuthenticatedByJWT::make($this)->mockLogin(User::factory()->create());


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
            $input,
        );


        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrorFor($errorMaker, 'errors.meta');
    }

    public static function invalidDataProvider(): array
    {
        return [
            'without email' => [
                'email',
                collect(self::validRequestInput())
                    ->except('email')
                    ->toArray(),
            ],
            'email is null' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => null,
                    ])->toArray(),
            ],
            'email is empty string' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => '',
                    ])->toArray(),
            ],
            'email is not in right format (now contain random string)' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => fake()->words(3, true),
                    ])->toArray(),
            ],
            'email is not in right format (now contain array)' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => [fake()->words(3, true)],
                    ])->toArray(),
            ],
            'email should be less than 250 (currently 251)' => [
                'email',
                collect(self::validRequestInput())
                    ->replace([
                        'email' => fake()->regexify('[a-z]{241}@gmail.com'),
                    ])->toArray(),
            ],

            'without name' => [
                'name',
                collect(self::validRequestInput())
                    ->except('name')
                    ->toArray(),
            ],
            'name is null' => [
                'name',
                collect(self::validRequestInput())
                    ->replace([
                        'name' => null,
                    ])->toArray(),
            ],
            'name is empty string' => [
                'name',
                collect(self::validRequestInput())
                    ->replace([
                        'name' => null,
                    ])->toArray(),
            ],
            'name is more than 250 (currently 251)' => [
                'name',
                collect(self::validRequestInput())
                    ->replace([
                        'name' => fake()->regexify('[a-z]{251}'),
                    ])->toArray(),
            ],

            'without password' => [
                'password',
                collect(self::validRequestInput())
                    ->except('password')
                    ->toArray(),
            ],
            'password is null' => [
                'password',
                collect(self::validRequestInput())
                    ->replace([
                        'password' => null,
                    ])->toArray(),
            ],
            'password is empty string' => [
                'password',
                collect(self::validRequestInput())
                    ->replace([
                        'password' => null,
                    ])->toArray(),
            ],
        ];
    }

    #[Test]
    public function should_show_401_when_not_logged_in(): void
    {
        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
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
                $mock->shouldReceive('update')->andReturn(false);
            }
        );



        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
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
            $userActor = User::factory()->create()->fresh(),
        );


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockException, $userActor) {
                $mock->shouldReceive('update')
                    ->once()
                    ->withArgs(fn (
                        UpdateUserPort $argInput
                    ) => $this->validateRequest(
                        $argInput,
                        $input,
                        $this->user,
                        $userActor,
                    ))->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        try {
            // Act
            $this->putJson(
                $this->getEndpointUrl($this->user->id),
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
            'email duplicate exception - 409' => [
                $e = new UserEmailDuplicatedException(new ExceptionMessageStandard(
                    $faker->sentence(),
                    $faker->sentence(),
                )),
                new ConflictException(
                    $e->exceptionMessage,
                    $e,
                ),
            ],
        ];
    }

    #[Test]
    public function should_show_200_when_successfully_update_user(): void
    {
        // Arrange
        $input = $this->validRequestInput();
        $mockedUser = User::factory()->create();

        MockerAuthenticatedByJWT::make($this)->mockLogin(
            $userActor = User::factory()->create(),
        );

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($input, $mockedUser, $userActor) {
                $mock->shouldReceive('update')
                    ->once()
                    ->withArgs(fn (
                        UpdateUserPort $argInput
                    ) => $this->validateRequest(
                        $argInput,
                        $input,
                        $this->user,
                        $userActor,
                    ))->andReturn($mockedUser);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->putJson(
            $this->getEndpointUrl($this->user->id),
            $input,
        );


        // Assert
        $response->assertOk();
        $response->assertJsonPath(
            'data',
            $this->jsonToArray(UserResource::make($mockedUser)->toJson()),
        );
    }

    protected function getEndpointUrl(int $userId): string
    {
        return route('user.update', ['userID' => $userId]);
    }

    protected function validateRequest(
        UpdateUserPort $argInput,
        array $input,
        User $userTarget,
        User $userActor,
    ): bool {
        try {
            $this->assertSame($input['email'], $argInput->getEmail());
            $this->assertSame($input['name'], $argInput->getName());
            $this->assertSame($input['password'], $argInput->getUserPassword());
            $this->assertTrue(
                $argInput->getUserModel()->is($userTarget),
                'User target not the same',
            );
            $this->assertTrue(
                $argInput->getUserActor()->is($userActor),
                'User actor not the same',
            );
            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }

    protected static function validRequestInput(): array
    {
        return [
            'email' => 'faisal@budiono.com',
            'name' => 'faisal budiono',
            'password' => 'password',
        ];
    }
}
