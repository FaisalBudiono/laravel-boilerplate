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
use Tests\Helper\MockInstance\Middleware\MockerAuthenticatedByJWT;
use Tests\Helper\ResourceAssertion\ResourceAssertion;
use Tests\Helper\ResourceAssertion\User\ResourceAssertionUser;

class GetMyInformationTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected User $mockUser;
    protected ResourceAssertion $resourceAssertion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resourceAssertion = new ResourceAssertionUser;

        $this->mockUser = User::factory()->create([
            'id' => $this->faker()->numberBetween(1, 100),
        ])->fresh();

        $this->instance(UserCoreContract::class, $this->mock(UserCoreContract::class));
    }

    #[Test]
    public function should_show_500_when_thrown_generic_error()
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageGeneric;

        $mockException = new Exception($this->faker->sentence());


        // Assert
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockUser)
            ->bindInstance();

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


        // Act
        $response = $this->getJson(
            $this->getEndpointUrl(),
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonPath('errors', $exceptionMessage->getJsonResponse()->toArray());
    }

    #[Test]
    public function should_show_200_when_successfully_fetch_user_information()
    {
        // Assert
        MockerAuthenticatedByJWT::make($this)
            ->mockLogin($this->mockUser)
            ->bindInstance();

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
        $this->resourceAssertion->assertResource($this, $response);
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
        } catch (Exception $e) {
            dd($e);
        }
    }
}
