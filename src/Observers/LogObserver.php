<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumpsCore\Actions\Trace;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\Dumper;

class LogObserver
{
    private array $trace = [];

    public function register(): void
    {
        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (!$this->isEnabled()) {
                return;
            }

            if ($message->level == 'debug') {
                $message->level = 'info';
            }

            if (!Config::get('send_logs_vendors')) {
                if (str_contains($message->message, 'vendor')) {
                    return;
                }
            }

            if (!Config::get('send_logs_deprecated')) {
                if (str_contains($message->message, 'deprecated')) {
                    return;
                }
            }

            if (Str::containsAll($message->message, ['From:', 'To:', 'Subject:'])) {
                return;
            }

            $log = [
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
        $this->trace = Trace::findSource()->toArray();

        return (bool) Config::get('send_logs_applications');
    }
}
