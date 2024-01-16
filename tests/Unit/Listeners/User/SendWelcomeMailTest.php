<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\User;

use App\Core\Formatter\Randomizer\Randomizer;
use App\Core\Logger\Message\Enum\LogEndpoint;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
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
            $this->faker->uuid(),
        );

        $mockedRequestID = $this->faker->uuid();
        $mockRandomizer = $this->mock(
            Randomizer::class,
            fn (MockInterface $mock) =>
            $mock->shouldReceive('getRandomizeString')->once()->andReturn($mockedRequestID)
        );
        assert($mockRandomizer instanceof Randomizer);

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

                $mock->shouldReceive('endpoint')->twice()->with(LogEndpoint::QUEUE->value)->andReturn($mock);
                $mock->shouldReceive('message')->twice()->with(SendWelcomeMail::class)->andReturn($mock);
                $mock->shouldReceive('requestID')->twice()->with($mockedRequestID)->andReturn($mock);
                $mock->shouldReceive('build')->twice()->andReturn($mockLogMessage);
            },
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);

        $mockLogDirector = MockerLogMessageDirector::make($this, $mockLogBuilder)
            ->normal(['buildBegin', 'buildSuccess'])
            ->build();

        $mockedException = new \Error($this->faker->sentence());

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
                randomizer: $mockRandomizer,
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
            $this->faker->uuid(),
        );

        $mockedRequestID = $this->faker->uuid();
        $mockRandomizer = $this->mock(
            Randomizer::class,
            fn (MockInterface $mock) =>
            $mock->shouldReceive('getRandomizeString')->once()->andReturn($mockedRequestID)
        );
        assert($mockRandomizer instanceof Randomizer);

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

                $mock->shouldReceive('endpoint')->twice()->with(LogEndpoint::QUEUE->value)->andReturn($mock);
                $mock->shouldReceive('message')->twice()->with(SendWelcomeMail::class)->andReturn($mock);
                $mock->shouldReceive('requestID')->twice()->with($mockedRequestID)->andReturn($mock);
                $mock->shouldReceive('build')->twice()->andReturn($mockLogMessage);
            },
        );
        assert($mockLogBuilder instanceof LogMessageBuilderContract);

        $mockLogDirector = MockerLogMessageDirector::make($this, $mockLogBuilder)
            ->normal(['buildBegin', 'buildSuccess'])
            ->build();

        Log::shouldReceive('info')
            ->with($mockLogMessage)
            ->twice();


        // Act
        $this->makeService(
            randomizer: $mockRandomizer,
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
        ?Randomizer $randomizer = null,
        ?LogMessageBuilderContract $logMessageBuilder = null,
        ?LogMessageDirectorContract $logMessageDirector = null,
    ): SendWelcomeMail {
        return new SendWelcomeMail(
            $randomizer ?? $this->mock(Randomizer::class),
            $logMessageBuilder ?? $this->mock(LogMessageBuilderContract::class),
            $logMessageDirector ?? $this->mock(LogMessageDirectorContract::class),
        );
    }
}
