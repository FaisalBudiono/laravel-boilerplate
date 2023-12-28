<?php

declare(strict_types=1);

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\User\UserCoreContract;
use App\Exceptions\Http\InternalServerErrorException;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Port\Core\User\GetUserPort;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
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
    }

    #[Test]
    public function should_show_500_when_thrown_generic_error(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $mockException = new \Error($this->faker->sentence());


        // Assert
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockUser);

        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(
                        fn (GetUserPort $argInput) => $this->validateRequest($argInput)
                    )->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);


        try {
            // Act
            $response = $this->getJson(
                $this->getEndpointUrl(),
            );
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            $expectedException = new InternalServerErrorException(
                new ExceptionMessageGeneric(),
                $mockException,
            );
            $this->assertEquals($expectedException, $e);
        }
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
                        fn (GetUserPort $argInput) => $this->validateRequest($argInput)
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

    protected function validateRequest(GetUserPort $argInput): bool
    {
        try {
            $this->assertEquals($this->mockUser, $argInput->getUserModel());
            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
