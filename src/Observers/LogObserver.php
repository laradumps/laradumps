<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumpsCore\Actions\{Config, Dumper};
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\CodeSnippet;

class LogObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen(MessageLogged::class, fn (MessageLogged $event) => $this->handle($event));
    }

    private function handle(MessageLogged $event): void
    {
        if (! $this->isEnabled('logs')) {
            return;
        }

        $normalizedLevel = $event->level === 'debug' ? 'info' : $event->level;

        if (! $this->shouldLogMessage($event->message, $normalizedLevel)) {
            return;
        }

        if (Str::containsAll($event->message, ['From:', 'To:', 'Subject:'])) {
            return;
        }

        $context = $this->resolveContext($event->context);

        $log = [
            'message' => $event->message,
            'level' => $normalizedLevel,
            'context' => Dumper::dump($context),
        ];

        $payload = new LogPayload($log);

        if (isset($event->context['exception']) && $event->context['exception'] instanceof \Throwable) {
            $snippet = (new CodeSnippet())->fromException($event->context['exception']);
            $payload->setCodeSnippet($snippet);
        }

        (new LaraDumps())->send($payload);
    }

    private function shouldLogMessage(string $message, string $level): bool
    {
        $config = (array) Config::get('logs', []);

        if (! isset($config[$level]) || $config[$level] !== true) {
            return false;
        }

        return match ($level) {
            'vendor' => str_contains($message, 'vendor'),
            'deprecated_message' => str_contains($message, 'deprecated'),
            default => true,
        };
    }

    private function resolveContext(?array $context): array
    {
        if (! blank($context)) {
            return $context;
        }

        if (class_exists(\Illuminate\Support\Facades\Context::class)) {
            return \Illuminate\Support\Facades\Context::all();
        }

        return [];
    }
}
