<?php

namespace App\Listeners\User;

use App\Core\Logger\Message\Enum\LogEndpoint;
use App\Core\Logger\Message\LogMessageBuilderContract;
use App\Core\Logger\Message\LogMessageDirectorContract;
use App\Core\Logger\Message\ProcessingStatus;
use App\Events\User\UserCreated;
use App\Mail\User\WelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeMail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected LogMessageBuilderContract $logMessageBuilder,
        protected LogMessageDirectorContract $logMessageDirector,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        try {
            Log::info(
                $this->logMessageDirector->buildHTTP(
                    $this->logMessageBuilder,
                    ProcessingStatus::BEGIN,
                )->requestID($event->requestID)
                    ->endpoint(LogEndpoint::QUEUE->value)
                    ->message(self::class)
                    ->meta([
                        'user' => $event->user->toArray(),
                    ])->build()
            );

            Mail::send(new WelcomeMail($event->user));

            Log::info(
                $this->logMessageDirector->buildHTTP(
                    $this->logMessageBuilder,
                    ProcessingStatus::SUCCESS,
                )->requestID($event->requestID)
                    ->endpoint(LogEndpoint::QUEUE->value)
                    ->message(self::class)
                    ->build()
            );
        } catch (\Throwable $e) {
            Log::error(
                $this->logMessageDirector->buildHTTP(
                    $this->logMessageDirector->buildForException(
                        $this->logMessageBuilder,
                        $e,
                    ),
                    ProcessingStatus::ERROR,
                )->requestID($event->requestID)
                    ->endpoint(LogEndpoint::QUEUE->value)
                    ->build()
            );
            throw $e;
        }
    }
}
