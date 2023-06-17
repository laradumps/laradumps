<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Payloads\NotificationPayload;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\Dumper;
use ReflectionClass;

class NotificationObserver
{
    public function register(): void
    {
        Event::listen(NotificationSent::class, function (NotificationSent $notificationSent) {
            if (!$this->isEnabled() || is_null($notificationSent->response)) {
                return;
            }

            /** @var SentMessage $sentMessage */
            $sentMessage = $notificationSent->response;

            $details = Dumper::dump([
                'notifiable'   => $notificationSent->notifiable,
                'notification' => $notificationSent->notification,
                'channel'      => $notificationSent->channel,
            ]);

            $dumps = new LaraDumps(trace: []);

            $dumps->send(new NotificationPayload($sentMessage, $details));
            $dumps->label('Notification - ' . $notificationSent->channel);
        });

        Event::listen(MessageSent::class, function (MessageSent $messageSent) {
            if (!$this->isEnabled()) {
                return;
            }

            $reflection = new ReflectionClass($messageSent);

            $sentProperty = $reflection->getProperty('sent');
            $sentProperty->setAccessible(true);

            /** @var SentMessage $sentMessage */
            $sentMessage = $sentProperty->getValue($messageSent);

            $dumps = new LaraDumps(trace: []);
            $dumps->send(new NotificationPayload($sentMessage, []));
            $dumps->label('Notification - Mail');
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('send_notifications');
    }
}
