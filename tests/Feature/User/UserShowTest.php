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

class UserShowTest extends BaseFeatureTestCase
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
                $mock->shouldReceive('see')->andReturn(true);
            }
        );
    }

    #[Test]
    public function should_show_401_when_not_logged_in(): void
    {
        // Act
        $response = $this->getJson(
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
                $mock->shouldReceive('see')->andReturn(false);
            }
        );



        // Act
        $response = $this->getJson(
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

        MockerAuthenticatedByJWT::make($this)->mockLogin(
            $userActor = User::factory()->create(),
        );

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException, $userActor) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(fn (
                        GetUserPort $argInput
                    ) => $this->validateRequest($argInput, $this->user, $userActor))
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        try {
            // Act
            $this->getJson(
                $this->getEndpointUrl($this->user->id),
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
    public function should_show_200_when_successfully_get_user_instance(): void
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();

        MockerAuthenticatedByJWT::make($this)->mockLogin(
            $userActor = User::factory()->create(),
        );

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockedUser, $userActor) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(fn (
                        GetUserPort $argInput
                    ) => $this->validateRequest($argInput, $this->user, $userActor))
                    ->andReturn($mockedUser);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl($this->user->id),
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
        return route('user.show', ['userID' => $userId]);
    }

    protected function validateRequest(
        GetUserPort $argInput,
        User $targetUser,
        User $loggedInUser,
    ): bool {
        try {
            $this->assertTrue(
                $argInput->getUserModel()->is($targetUser),
                'target user is not the same',
            );
            $this->assertTrue(
                $argInput->getUserActor()->is($loggedInUser),
                'user actor not the same',
            );
            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
