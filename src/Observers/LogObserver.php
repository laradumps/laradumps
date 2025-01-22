<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumpsCore\Actions\{Config, Dumper};
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\CodeSnippet;

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

            $logs = (array) Config::get('logs');

            $shouldReturn = [];

            collect($logs)
                ->map(function ($value, $key) use ($message, &$shouldReturn) {
                    /** @var string $key */
                    if ($message->level === $key & $value === true) {
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

            if (!in_array($message->level, $shouldReturn)) {
                return;
            }

            if (Str::containsAll($message->message, ['From:', 'To:', 'Subject:'])) {
                return;
            }

            $dumps = new LaraDumps();

            $context = $message->context;

            if (blank($message->context) && class_exists(\Illuminate\Support\Facades\Context::class)) {
                $context = \Illuminate\Support\Facades\Context::all();
            }

            $log = [
                'message' => $message->message,
                'level'   => $message->level,
                'context' => Dumper::dump($context),
            ];

            $payload = new LogPayload($log);

            if (isset($message->context['exception'])) {
                /** @var \Exception $exception */
                $exception = $message->context['exception'];

                $context = (new CodeSnippet())->fromException($exception);

                $payload->setCodeSnippet($context);
            }

            $dumps->send($payload);
        });
    }

    public function isEnabled(): bool
    {
        return (bool) Config::get('observers.logs', false);
    }
}
