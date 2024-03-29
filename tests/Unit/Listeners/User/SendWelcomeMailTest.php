<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\User;

use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Core\Logger\Message\ProcessingStatus;
use App\Core\Logger\Message\ValueObject\LogMessage;
use App\Events\User\UserCreated;
use App\Listeners\User\SendWelcomeMail;
use App\Mail\User\WelcomeMail;
use App\Models\User\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helper\MockInstance\Core\Logger\Message\MockerLogMessageDirector;
use Tests\TestCase;

class SendWelcomeMailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Log::partialMock();
    }

    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(ShouldQueue::class, $this->makeService());
    }

    #[Test]
    public function should_throw_error_and_log_the_error_appropriately(): void
    {
        // Arrange
        $event = new UserCreated(
            $user = User::factory()->create(),
            $mockedRequestID = $this->faker->uuid(),
        );

        $mockedException = new \Error($this->faker->sentence());

        $mockLogMessage = $this->mock(LogMessage::class);
        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockLogMessage,
                $user,
            ) {
                $mock->shouldReceive('meta')->once()->with([
                    'user' => $user->toArray(),
                ])->andReturn($mock);

                $mock->shouldReceive('build')->twice()->andReturn($mockLogMessage);
            },
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);

        $mockLogDirector = MockerLogMessageDirector::make($this, $mockLogBuilder)
            ->queue(
                ProcessingStatus::BEGIN,
                SendWelcomeMail::class,
                $mockedRequestID,
            )->queue(
                ProcessingStatus::ERROR,
                SendWelcomeMail::class,
                $mockedRequestID,
            )->forException($mockedException)
            ->build();

        Log::shouldReceive('info')
            ->with($mockLogMessage)
            ->once()
            ->andThrow($mockedException);

        Log::shouldReceive('error')
            ->with($mockLogMessage)
            ->once();


        try {
            // Act
            $this->makeService(
                logMessageBuilder: $mockLogBuilder,
                logMessageDirector: $mockLogDirector,
            )->handle($event);
            $this->fail('Should throw error');
        } catch (AssertionFailedError $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Arrange
            $this->assertEquals($mockedException, $e);
            Mail::assertNothingQueued();
        }
    }

    #[Test]
    public function should_send_welcome_mail(): void
    {
        // Arrange
        $event = new UserCreated(
            $user = User::factory()->create(),
            $mockedRequestID = $this->faker->uuid(),
        );

        $mockLogMessage = $this->mock(LogMessage::class);
        $mockLogBuilder = $this->mock(
            LogMessageBuilderContract::class,
            function (MockInterface $mock) use (
                $mockedRequestID,
                $mockLogMessage,
                $user,
            ) {
                $mock->shouldReceive('meta')->once()->with([
                    'user' => $user->toArray(),
                ])->andReturn($mock);

                $mock->shouldReceive('build')->twice()->andReturn($mockLogMessage);
            },
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);

        $mockLogDirector = MockerLogMessageDirector::make($this, $mockLogBuilder)
            ->queue(
                ProcessingStatus::BEGIN,
                SendWelcomeMail::class,
                $mockedRequestID,
            )->queue(
                ProcessingStatus::SUCCESS,
                SendWelcomeMail::class,
                $mockedRequestID,
            )->build();

        Log::shouldReceive('info')
            ->with($mockLogMessage)
            ->twice();


        // Act
        $this->makeService(
            logMessageBuilder: $mockLogBuilder,
            logMessageDirector: $mockLogDirector,
        )->handle($event);


        // Arrange
        Mail::assertQueued(WelcomeMail::class, function (WelcomeMail $mail) use ($user) {
            $this->assertTrue($user->is($mail->user));
            return true;
        });
    }

    protected function makeService(
        ?LogMessageBuilderContract $logMessageBuilder = null,
        ?LogMessageDirectorContract $logMessageDirector = null,
    ): SendWelcomeMail {
        return new SendWelcomeMail(
            $logMessageBuilder ?? $this->mock(LogMessageBuilderContract::class),
            $logMessageDirector ?? $this->mock(LogMessageDirectorContract::class),
        );
    }
}
