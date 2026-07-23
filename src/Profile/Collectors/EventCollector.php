<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\ProfileManager;

class EventCollector
{
    private array $ignoredPatterns = [
        'Illuminate\Log\Events\*',
        'Illuminate\Database\Events\*',
        'Illuminate\Cache\Events\*',
        'Illuminate\Http\Client\Events\*',
        'Illuminate\Queue\Events\*',
        'Illuminate\View\Events\*',
        'Illuminate\Routing\Events\*',
        'eloquent.*',
        'bootstrapped: *',
        'composing: *',
        'creating: *',
    ];

    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen('*', function (string $eventName, array $payload) {
            $this->handle($eventName, $payload);
        });
    }

    private function handle(string $eventName, array $payload): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('events')) {
            return;
        }

        if ($this->shouldIgnore($eventName)) {
            return;
        }

        $name = $this->buildName($eventName);

        $metadata = [
            'event' => $eventName,
            'payload_type' => isset($payload[0]) ? get_class($payload[0]) : null,
        ];

        $origin = $this->manager->captureBacktrace();

        $this->manager->tracer()?->instantSpan('event', $name, 0, $metadata, $origin);
    }

    private function buildName(string $eventName): string
    {
        if (class_exists($eventName)) {
            $shortName = class_basename($eventName);

            return "event({$shortName})";
        }

        return "event({$eventName})";
    }

    private function shouldIgnore(string $eventName): bool
    {
        foreach ($this->ignoredPatterns as $pattern) {
            if (str_ends_with($pattern, '*')) {
                $prefix = rtrim($pattern, '*');
                if (str_starts_with($eventName, $prefix)) {
                    return true;
                }
            } elseif ($eventName === $pattern) {
                return true;
            }
        }

        return false;
    }
}
