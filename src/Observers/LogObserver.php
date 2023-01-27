<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Trace;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumps\Support\Dumper;

class LogObserver
{
    private array $trace = [];

    public function register(): void
    {
        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (!$this->isEnabled()) {
                return;
            }

            if (str_contains($message->message, '/vendor/laravel/framework')) {
                return;
            }

            if ($message->level == 'debug') {
                $message->level = 'info';
            }

            $log       = [
                'message' => $message->message,
                'level'   => $message->level,
                'context' => Dumper::dump($message->context),
            ];

            $dumps = new LaraDumps(trace: $this->trace);

            $dumps->send(new LogPayload($log));

            $dumps->toScreen('Logs');
        });
    }

    public function isEnabled(): bool
    {
        $this->trace   = Trace::findSource()->toArray();

        return (bool) config('laradumps.send_log_applications');
    }
}
