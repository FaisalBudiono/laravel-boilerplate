<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionErrorCode;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Formatter\ExceptionMessage\ExceptionMessageStandard;
use App\Core\User\Policy\UserPolicyContract;
use App\Core\User\UserCoreContract;
use App\Exceptions\Core\Auth\Permission\InsufficientPermissionException;
use App\Exceptions\Http\AbstractHttpException;
use App\Exceptions\Http\ForbiddenException;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Port\Core\User\GetUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;
use Tests\Helper\Trait\JSONTrait;

class MeTest extends BaseFeatureTestCase
{
    use JSONTrait;

    protected User $mockUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockUser = User::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ])->fresh();

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));

        $this->mock(
            UserPolicyContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('see')->andReturn(true);
            }
        );
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
                $mock->shouldReceive('see')->andReturn(false);
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

        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockUser);

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(
                        fn (GetUserPort $argInput) => $this->validateRequest($argInput, $this->mockUser)
                    )->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        try {
            // Act
            $this->getJson(
                $this->getEndpointUrl(),
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
    public function should_show_200_when_successfully_fetch_user_information(): void
    {
        // Assert
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockUser);

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(
                        fn (GetUserPort $argInput) => $this->validateRequest($argInput, $this->mockUser)
                    )->andReturn($this->mockUser);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
        );


        // Assert
        $response->assertOk();
        $response->assertJsonPath(
            'data',
            $this->jsonToArray(UserResource::make($this->mockUser)->toJson()),
        );
    }

    protected function getEndpointUrl(): string
    {
        return route('me');
    }

    protected function validateRequest(
        GetUserPort $argInput,
        User $loggedInUser,
    ): bool {
        try {
            $this->assertEquals($loggedInUser, $argInput->getUserModel());
            $this->assertEquals($loggedInUser, $argInput->getUserActor());
            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
