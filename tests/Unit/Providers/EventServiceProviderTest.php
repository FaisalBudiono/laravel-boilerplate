<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Events\User\UserCreated;
use App\Listeners\User\SendWelcomeMail;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    #[Test]
    public function should_register_all_events_and_listeners(): void
    {
        // Arrange
        collect($this->eventListeners())->each(function (array $listeners, string $event) {
            collect($listeners)->each(
                fn ($listener) => Event::assertListening($event, $listener),
            );
        });
    }

    protected function eventListeners(): array
    {
        return [
            UserCreated::class => [
                SendWelcomeMail::class,
            ],
        ];
    }
}
