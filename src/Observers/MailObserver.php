<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Payloads\MailPayload;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;

class MailObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen(MessageSent::class, fn (MessageSent $event) => $this->handleMessageSent($event));
        Event::listen(NotificationSent::class, fn (NotificationSent $event) => $this->handleNotificationSent($event));
    }

    private function handleMessageSent(MessageSent $event): void
    {
        if (! $this->isEnabled('mail')) {
            return;
        }

        $payload = new MailPayload(
            $event->sent,
            Dumper::dump($event->data),
            $event->sent->getMessageId()
        );

        (new LaraDumps())->send($payload);
    }

    private function handleNotificationSent(NotificationSent $event): void
    {
        if (! $this->isEnabled('mail')) {
            return;
        }

        if (! $this->isValidSentMessage($event->response)) {
            return;
        }

        /** @var SentMessage $sentMessage */
        $sentMessage = $event->response;

        $details = Dumper::dump([
            'notifiable' => $event->notifiable,
            'notification' => $event->notification,
            'channel' => $event->channel,
        ]);

        $payload = new MailPayload(
            $sentMessage,
            $details,
            $sentMessage->getMessageId(),
            label: $event->channel
        );

        (new LaraDumps())->send($payload);
    }

    private function isValidSentMessage(mixed $response): bool
    {
        return $response instanceof SentMessage;
    }
}
