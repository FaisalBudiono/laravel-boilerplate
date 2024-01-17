<?php

namespace App\Listeners\User;

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
                $this->logMessageDirector->buildQueue(
                    $this->logMessageBuilder,
                    ProcessingStatus::BEGIN,
                    self::class,
                    $event->requestID,
                )->meta([
                    'user' => $event->user->toArray(),
                ])->build()
            );

            Mail::send(new WelcomeMail($event->user));

            Log::info(
                $this->logMessageDirector->buildQueue(
                    $this->logMessageBuilder,
                    ProcessingStatus::SUCCESS,
                    self::class,
                    $event->requestID,
                )->build()
            );
        } catch (\Throwable $e) {
            Log::error(
                $this->logMessageDirector->buildQueue(
                    $this->logMessageDirector->buildForException(
                        $this->logMessageBuilder,
                        $e,
                    ),
                    ProcessingStatus::ERROR,
                    self::class,
                    $event->requestID,
                )->build()
            );
            throw $e;
        }
    }
}
