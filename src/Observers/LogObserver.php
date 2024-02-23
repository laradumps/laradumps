<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\Dumper;
use Spatie\Backtrace\Backtrace;

class LogObserver
{
    use Traceable;

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

            $backtrace = Backtrace::create();

            $backtrace = $backtrace->applicationPath(base_path());

            $frame = $this->parseFrame($backtrace);

            if (empty($frame)) {
                return;
            }

            $dumps = new LaraDumps();

            $log = [
                'message' => $message->message,
                'level'   => $message->level,
                'context' => Dumper::dump($message->context),
            ];

            $payload = new LogPayload($log);
            $payload->setFrame($frame);

            $dumps->send($payload);

            $dumps->toScreen('Logs');
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('send_logs_applications');
    }
}
