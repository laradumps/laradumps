<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Payloads\MailPayload;
use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\Dumper;

class MailObserver
{
    public function register(): void
    {
        Event::listen(MessageSent::class, function (MessageSent $messageSent) {
            if (!$this->isEnabled()) {
                return;
            }

            if (!method_exists($messageSent->sent, 'getMessageId')) {
                return;
            }

            $dumps = new LaraDumps();

            $payload = new MailPayload($messageSent->sent, Dumper::dump($messageSent->data), $messageSent->sent->getMessageId());

            $dumps->send($payload);
            $dumps->label('Notification - Mail');
        });

        Event::listen(NotificationSent::class, function (NotificationSent $notificationSent) {
            if (!$this->isEnabled() || is_null($notificationSent->response)) {
                return;
            }

            /** @var SentMessage $sentMessage */
            $sentMessage = $notificationSent->response;

            if (!$sentMessage instanceof SentMessage) {
                return;
            }

            $details = Dumper::dump([
                'notifiable'   => $notificationSent->notifiable,
                'notification' => $notificationSent->notification,
                'channel'      => $notificationSent->channel,
            ]);

            $dumps = new LaraDumps();

            $payload = new MailPayload($sentMessage, $details, $sentMessage->getMessageId());

            $dumps->send($payload);
            $dumps->label('Notification - ' . $notificationSent->channel);
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('observers.mail');
    }
}
