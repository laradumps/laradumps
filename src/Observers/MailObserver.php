<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\SentMessage;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Payloads\MailPayload;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\Dumper;
use Spatie\Backtrace\Backtrace;

class MailObserver
{
    use Traceable;

    public function register(): void
    {
        Event::listen(MessageSent::class, function (MessageSent $messageSent) {
            if (!$this->isEnabled()) {
                return;
            }

            $backtrace = Backtrace::create();
            $backtrace = $backtrace->applicationPath(base_path());
            $frame     = $this->parseFrame($backtrace);

            $dumps = new LaraDumps();

            $payload = new MailPayload($messageSent->sent, Dumper::dump($messageSent->data), $messageSent->sent->getMessageId());
            $payload->setFrame($frame);

            $dumps->send($payload);
            $dumps->label('Notification - Mail');
        });

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

            $backtrace = Backtrace::create();
            $backtrace = $backtrace->applicationPath(base_path());
            $frame     = $this->parseFrame($backtrace);

            $dumps = new LaraDumps();

            $payload = new MailPayload($sentMessage, $details, $sentMessage->getMessageId());
            $payload->setFrame($frame);

            $dumps->send($payload);
            $dumps->label('Notification - ' . $notificationSent->channel);
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('send_mail');
    }
}
