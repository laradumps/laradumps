<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Payloads\NotificationPayload;
use LaraDumps\LaraDumpsCore\Actions\Trace;
use LaraDumps\LaraDumpsCore\LaraDumps;

class NotificationObserver
{
    private array $trace = [];

    public function register(): void
    {
        Event::listen(NotificationSent::class, function (NotificationSent $notificationSent) {
            if (!$this->isEnabled()) {
                return;
            }

            $dumps = new LaraDumps(trace: $this->trace);

            $dumps->send(new NotificationPayload($notificationSent));
        });
    }

    public function isEnabled(): bool
    {
        $this->trace = Trace::findSource()->toArray();

        return (bool) Config::get('send_notifications');
    }
}
