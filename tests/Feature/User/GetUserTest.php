<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\GetUserPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\Helper\ResourceAssertion\User\ResourceAssertionUser;

class GetUserTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ResourceAssertion $resourceAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceAssertion = new ResourceAssertionUser;

        $this->user = User::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ]);

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
    }

    #[Test]
    public function should_show_500_when_thrown_generic_error()
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageGeneric;

        $mockException = new Exception($this->faker->sentence());


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


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl($this->user->id),
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonPath('errors', $exceptionMessage->getJsonResponse()->toArray());
    }

    #[Test]
    public function should_show_200_when_successfully_get_user_instance()
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
        $this->resourceAssertion->assertResource($this, $response);
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
        } catch (Exception $e) {
            dd($e);
        }
    }
}
