<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Cache\Events\{CacheHit, CacheMissed, KeyForgotten, KeyWritten};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\ProfileManager;

readonly class CacheCollector
{
    public function __construct(
        private ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen(CacheHit::class, fn ($event) => $this->handle('hit', $event));
        Event::listen(CacheMissed::class, fn ($event) => $this->handle('miss', $event));
        Event::listen(KeyWritten::class, fn ($event) => $this->handle('write', $event));
        Event::listen(KeyForgotten::class, fn ($event) => $this->handle('forget', $event));
    }

    private function handle(string $action, object $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('cache')) {
            return;
        }

        $key = $event->key ?? 'unknown';
        $shortKey = strlen($key) > 30 ? substr($key, 0, 30).'...' : $key;

        $name = "cache({$action}: {$shortKey})";

        $metadata = [
            'action' => $action,
            'key' => $key,
            'tags' => $event->tags ?? [],
        ];

        $origin = $this->manager->captureBacktrace();

        $this->manager->tracer()?->instantSpan('cache', $name, 0, $metadata, $origin);
    }
}
