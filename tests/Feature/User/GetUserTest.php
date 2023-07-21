<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\GetUserPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\MockerLoggerMessageFactory;
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
        $this->instance(
            LoggerMessageFactoryContract::class,
            $this->mock(LoggerMessageFactoryContract::class),
        );
        Log::partialMock();
    }

    #[Test]
    public function should_show_500_when_thrown_generic_error()
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageGeneric;

        $mockException = new Exception($this->faker->sentence());

        $logInfoMessage = $this->faker->sentence;
        $logErrorMessage = $this->faker->sentence;


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

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Show user endpoint',
                [],
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

        $logInfoMessage = $this->faker->sentence;
        $logSuccessMessage = $this->faker->sentence;


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

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Show user endpoint',
                [],
                $logInfoMessage,
            )->setHTTPSuccess(
                'Show user endpoint',
                [],
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
