<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\Message\LoggerMessageFactoryContract;
use App\Core\User\UserCoreContract;
use App\Models\User\User;
use App\Port\Core\User\DeleteUserPort;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\BaseFeatureTestCase;
use Tests\Helper\MockInstance\MockerLoggerMessageFactory;

class DeleteUserTest extends BaseFeatureTestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

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
    public function should_show_404_when_user_id_is_not_found()
    {
        // Arrange
        $notFoundId = $this->user->id + 1;


        // Act
        $response = $this->deleteJson(
            $this->getEndpointUrl($notFoundId),
        );


        // Assert
        $response->assertNotFound();
    }

    #[Test]
    public function should_show_500_when_thrown_generic_error()
    {
        // Arrange
        $exceptionMessage = new ExceptionMessageGeneric;

        $logInfoMessage = $this->faker->sentence;
        $logErrorMessage = $this->faker->sentence;
        $mockException = new Exception('generic error');


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) use ($mockException) {
                $mock->shouldReceive('delete')
                    ->once()
                    ->withArgs(fn (
                        DeleteUserPort $argInput
                    ) => $this->validateRequest($argInput))
                    ->andThrow($mockException);
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Delete user endpoint',
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
        $response = $this->deleteJson(
            $this->getEndpointUrl($this->user->id),
        );


        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJsonPath('errors', $exceptionMessage->getJsonResponse()->toArray());
    }

    #[Test]
    public function should_show_204_when_successfully_delete_user()
    {
        // Arrange
        $logInfoMessage = $this->faker->sentence;
        $logSuccessMessage = $this->faker->sentence;


        // Assert
        $mockCore = $this->mock(
            UserCoreContract::class,
            function (MockInterface $mock) {
                $mock->shouldReceive('delete')
                    ->once()
                    ->withArgs(fn (
                        DeleteUserPort $argInput
                    ) => $this->validateRequest($argInput));
            }
        );
        $this->instance(UserCoreContract::class, $mockCore);

        MockerLoggerMessageFactory::make($this)
            ->setHTTPStart(
                'Delete user endpoint',
                [],
                $logInfoMessage,
            )->setHTTPSuccess(
                'Delete user endpoint',
                [],
                $logSuccessMessage
            )->bindInstance();

        Log::shouldReceive('info')
            ->withArgs(function ($argMessage) use ($logInfoMessage) {
                try {
                    $this->assertEquals($logInfoMessage, $argMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();
        Log::shouldReceive('info')
            ->withArgs(function ($argMessage) use ($logSuccessMessage) {
                try {
                    $this->assertEquals($logSuccessMessage, $argMessage);
                    return true;
                } catch (Exception $e) {
                    dd($e);
                }
            })->once();


        // Act
        $response = $this->deleteJson(
            $this->getEndpointUrl($this->user->id),
        );


        // Assert
        $response->assertNoContent();
    }

    protected function getEndpointUrl(int $userId): string
    {
        return route('user.destroy', ['userID' => $userId]);
    }

    protected function validateRequest(DeleteUserPort $argInput): bool
    {
        try {
            $this->assertTrue($argInput->getUserModel()->is($this->user));
            return true;
        } catch (Exception $e) {
            dd($e);
        }
    }
}
