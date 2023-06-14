<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Payloads\NotificationPayload;
use LaraDumps\LaraDumpsCore\LaraDumps;

class NotificationObserver
{
    public function register(): void
    {
        Event::listen(NotificationSent::class, function (NotificationSent $notificationSent) {
            if (!$this->isEnabled()) {
                return;
            }

            $dumps = new LaraDumps(trace: []);

            $dumps->send(new NotificationPayload($notificationSent));
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('send_notifications');
    }
}
