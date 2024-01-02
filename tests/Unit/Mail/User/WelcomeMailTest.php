<?php

declare(strict_types=1);

namespace Tests\Unit\Mail\User;

use App\Mail\User\WelcomeMail;
use App\Models\User\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WelcomeMailTest extends TestCase
{
    #[Test]
    public function should_have_correct_content(): void
    {
        // Arrange
        $user = User::factory()->create();


        // Act
        $mail = new WelcomeMail($user);


        // Assert
        $this->assertInstanceOf(ShouldQueue::class, $mail);

        $mail->assertHasSubject('Welcome Mail');
        $mail->assertFrom('no-reply@example.com', 'Mr. Example');
        $mail->assertTo($user->email, $user->name);

        $mail->assertSeeInHtml($user->name);
    }
}
