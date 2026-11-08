<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

class RequestObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen('Laravel\Octane\Events\RequestReceived', fn () => Payload::startRequest());

        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            if ($event->connectionName !== 'sync') {
                Payload::startRequest();
            }
        });
    }
}
