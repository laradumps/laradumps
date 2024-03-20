<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumpsCore\Actions\{Config, Dumper};
use LaraDumps\LaraDumpsCore\LaraDumps;

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

            $logs = Config::get('logs');

            $shouldReturn = [];

            collect($logs)
                ->map(function ($value, $key) use ($message, &$shouldReturn) {
                    if ($message->level == $key && strval($value) == '1') {
                        if ($key === 'vendor') {
                            if (str_contains($message->message, 'vendor')) {
                                $shouldReturn[] = $key;
                            }
                        } elseif ($key === 'deprecated_message') {
                            if (str_contains($message->message, 'deprecated')) {
                                $shouldReturn[] = $key;
                            }
                        } else {
                            $shouldReturn[] = $key;
                        }
                    }
                });

            if (in_array($message->level, $shouldReturn)) {
                return;
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
