<?php

namespace Tests\Feature\User;

use App\Core\Formatter\ExceptionMessage\ExceptionMessageGeneric;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatter;
use App\Core\Logger\MessageFormatter\LoggerMessageFormatterFactoryContract;
use App\Core\Logger\MessageFormatter\ProcessingStatus;
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
            LoggerMessageFormatterFactoryContract::class,
            $this->mock(LoggerMessageFormatterFactoryContract::class)
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

        $mockException = new Exception('generic error');

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

        $logInfoValue = $this->faker->sentence;
        $logErrorValue = $this->faker->sentence;

        $mockLoggerFormatterFactory = $this->mock(
            LoggerMessageFormatterFactoryContract::class,
            function (MockInterface $mock) use (
                $logInfoValue,
                $logErrorValue,
                $mockException,
            ) {
                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingBegin(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo($this->user->id),
                        'Delete user endpoint',
                        [],
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logInfoValue)
                        )
                    );

                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingError(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo($this->user->id),
                        $mockException,
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logErrorValue)
                        )
                    );
            }
        );
        $this->instance(
            LoggerMessageFormatterFactoryContract::class,
            $mockLoggerFormatterFactory
        );

        Log::shouldReceive('info')
            ->with($logInfoValue)
            ->once();
        Log::shouldReceive('error')
            ->with($logErrorValue)
            ->once();


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

        $logInfoValue = $this->faker->sentence;
        $logSuccessValue = $this->faker->sentence;

        $mockLoggerFormatterFactory = $this->mock(
            LoggerMessageFormatterFactoryContract::class,
            function (MockInterface $mock) use (
                $logInfoValue,
                $logSuccessValue,
            ) {
                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingBegin(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo($this->user->id),
                        'Delete user endpoint',
                        [],
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logInfoValue)
                        )
                    );

                $mock->shouldReceive('makeGeneric')
                    ->once()
                    ->withArgs(fn (
                        string $argEndpoint,
                        string $argRequestID,
                        ProcessingStatus $argProcessingStatus,
                        string $argMessage,
                        array $argMeta,
                    ) => $this->validateLoggingSuccess(
                        $argEndpoint,
                        $argRequestID,
                        $argProcessingStatus,
                        $argMessage,
                        $argMeta,
                        $this->getEndpointInfo($this->user->id),
                        'Delete user endpoint',
                    ))->andReturn(
                        $this->mock(
                            LoggerMessageFormatter::class,
                            fn (MockInterface $mock) => $mock->shouldReceive('getMessage')
                                ->once()->andReturn($logSuccessValue)
                        )
                    );
            }
        );
        $this->instance(
            LoggerMessageFormatterFactoryContract::class,
            $mockLoggerFormatterFactory
        );

        Log::shouldReceive('info')
            ->with($logInfoValue)
            ->once();
        Log::shouldReceive('info')
            ->with($logSuccessValue)
            ->once();

        // Act
        $response = $this->deleteJson(
            $this->getEndpointUrl($this->user->id),
        );


        // Assert
        $response->assertNoContent();
    }

    protected function getEndpointInfo(int $userId): string
    {
        return "DELETE {$this->getEndpointUrl($userId)}";
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
