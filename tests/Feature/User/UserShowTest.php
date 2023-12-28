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
    }

    #[Test]
    public function should_show_500_when_thrown_generic_error(): void
    {
        // Arrange
        $this->withoutExceptionHandling();

        $mockException = new \Error($this->faker->sentence());


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(fn (
                        GetUserPort $argInput
                    ) => $this->validateRequest($argInput))
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
            $expectedException = new InternalServerErrorException(
                new ExceptionMessageGeneric(),
                $mockException,
            );
            $this->assertEquals($expectedException, $e);
        }
    }


    #[Test]
    public function should_show_200_when_successfully_get_user_instance(): void
    {
        // Arrange
        $mockedUser = User::factory()->create()->fresh();


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockedUser) {
                $mock->shouldReceive('get')
                    ->once()
                    ->withArgs(fn (
                        GetUserPort $argInput
                    ) => $this->validateRequest($argInput))
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

    protected function validateRequest(GetUserPort $argInput): bool
    {
        try {
            $this->assertTrue($argInput->getUserModel()->is($this->user));
            return true;
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}
