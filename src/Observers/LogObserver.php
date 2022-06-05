<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumps\Support\Dumper;

class LogObserver
{
    public function register(): void
    {
        Event::listen(MessageLogged::class, function (MessageLogged $message) {
            if (!$this->isEnabled()) {
                return;
            }

            $dumps = new LaraDumps();

            /** @var array $config */
            $config    = config('laradumps.level_log_colors_map');
            $log       = [
                'message'     => $message->message,
                'level'       => $message->level,
                'level_color' => $config[$message->level],
                'context'     => Dumper::dump($message->context),
            ];

            $dumps->send(new LogPayload($log));
        });
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_log_applications');
    }
}
