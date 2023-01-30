<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Cache\Events\{CacheEvent, CacheHit, CacheMissed, KeyForgotten, KeyWritten};
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Concerns\Traceable;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\TableV2Payload;

class CacheObserver
{
    use Traceable;

    protected ?string $label = null;

    protected array $hidden = [];

    private bool $enabled = false;

    private array $trace = [];

    public function register(): void
    {
        if (!($this->enabled = $this->isEnabled())) {
            return;
        }

        Event::listen(CacheHit::class, function (CacheHit $event) {
            $this->sendCache($event, [
                'type'  => 'hit',
                'key'   => $event->key,
                'value' => $this->formatValue($event),
            ]);
        });

        Event::listen(CacheMissed::class, function (CacheMissed $event) {
            $this->sendCache($event, [
                'type' => 'missed',
                'key'  => $event->key,
            ]);
        });

        Event::listen(KeyForgotten::class, function (KeyForgotten $event) {
            $this->sendCache($event, [
                'type' => 'forget',
                'key'  => $event->key,
            ]);
        });

        Event::listen(KeyWritten::class, function (KeyWritten $event) {
            $this->sendCache($event, [
                'type'       => 'set',
                'key'        => $event->key,
                'value'      => $this->formatValue($event),
                'expiration' => $this->formatExpiration($event),
            ]);
        });
    }

    protected function sendCache(CacheEvent $event, array $data): void
    {
        if (!$this->isEnabled() || $this->shouldIgnore($event)) {
            return;
        }

        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send(
            new TableV2Payload($data)
        );

        if (!empty($this->label)) {
            $dumps->label($this->label);
        }
    }

    public function enable(string $label = null): void
    {
        $this->label = $label ?? 'Cache';

        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        $this->trace = array_slice($this->findSource(), 0, 5)[0] ?? [];

        if (!boolval(config('laradumps.send_cache'))) {
            return $this->enabled;
        }

        return true;
    }

    public function hidden(array $hidden = []): array
    {
        if (!empty($hidden)) {
            $this->hidden = array_merge($hidden);
        }

        return $this->hidden ?? [];
    }

    private function formatValue(mixed $event): mixed
    {
        return (!$this->shouldHideValue($event))
            /* @phpstan-ignore-next-line */
            ? $event->value
            : '********';
    }

    private function shouldHideValue(mixed $event): bool
    {
        return Str::is(
            $this->hidden(),
            /* @phpstan-ignore-next-line */
            $event->key
        );
    }

    protected function formatExpiration(KeyWritten $event): mixed
    {
        return property_exists($event, 'seconds')
            ? $event->seconds
            /* @phpstan-ignore-next-line */
            : $event->minutes * 60;
    }

    private function shouldIgnore(mixed $event): bool
    {
        return Str::is(
            [
                'illuminate:queue:restart',
                'framework/schedule*',
                'telescope:*',
            ],
            /* @phpstan-ignore-next-line */
            $event->key
        );
    }
}
