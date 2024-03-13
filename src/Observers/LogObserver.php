<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\Dumper;

class LogObserver
{
    public function register(): void
    {
        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (!$this->isEnabled()) {
                return;
            }

            if ($message->level == 'debug') {
                $message->level = 'info';
            }

            if (!Config::get('observers.logs_vendor', false)) {
                if (str_contains($message->message, 'vendor')) {
                    return;
                }
            }

            if (!Config::get('observers.logs_deprecated', false)) {
                if (str_contains($message->message, 'deprecated')) {
                    return;
                }
            }

            if (Str::containsAll($message->message, ['From:', 'To:', 'Subject:'])) {
                return;
            }

            $dumps = new LaraDumps();

            $log = [
                'message' => $message->message,
                'level'   => $message->level,
                'context' => Dumper::dump($message->context),
            ];

            $payload = new LogPayload($log);

            $dumps->send($payload);

            $dumps->toScreen('Logs');
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('observers.logs_applications', false);
    }
}
