<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\User;

use App\Events\User\UserCreated;
use App\Listeners\User\SendWelcomeMail;
use App\Mail\User\WelcomeMail;
use App\Models\User\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendWelcomeMailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(ShouldQueue::class, $this->makeService());
    }

    #[Test]
    public function should_send_welcome_mail(): void
    {
        // Arrange
        $event = new UserCreated($user = User::factory()->create());


        // Act
        $this->makeService()->handle($event);


        // Arrange
        Mail::assertQueued(WelcomeMail::class, function (WelcomeMail $mail) use ($user) {
            $this->assertTrue($user->is($mail->user));
            return true;
        });
    }

    protected function makeService(): SendWelcomeMail
    {
        return new SendWelcomeMail();
    }
}
