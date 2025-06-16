<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Cache\Events\{CacheEvent, CacheHit, CacheMissed, KeyForgotten, KeyWritten};
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\TableV2Payload;

class CacheObserver extends BaseObserver
{
    protected string $label = 'Cache';

    protected array $hidden = [];

    public function register(): void
    {
        Event::listen(CacheHit::class, fn (CacheHit $event) => $this->handleHit($event));
        Event::listen(CacheMissed::class, fn (CacheMissed $event) => $this->handleMissed($event));
        Event::listen(KeyForgotten::class, fn (KeyForgotten $event) => $this->handleForgotten($event));
        Event::listen(KeyWritten::class, fn (KeyWritten $event) => $this->handleWritten($event));
    }

    public function hidden(array $hidden = []): array
    {
        if (! empty($hidden)) {
            $this->hidden = array_merge($hidden);
        }

        return $this->hidden;
    }

    public function handleHit(CacheHit $event): void
    {
        $this->sendCache($event, [
            'Type' => 'hit',
            'Key' => $event->key,
            'Value' => $this->formatValue($event),
        ], 'width: 120px', 'Cache Hit');
    }

    public function handleMissed(CacheMissed $event): void
    {
        $this->sendCache($event, [
            'Type' => 'missed',
            'Key' => $event->key,
        ], 'width: 120px', 'Cache Missed');
    }

    public function handleForgotten(KeyForgotten $event): void
    {
        $this->sendCache($event, [
            'Type' => 'forget',
            'Key' => $event->key,
        ], 'width: 120px', 'Cache Forgot');
    }

    public function handleWritten(KeyWritten $event): void
    {
        $this->sendCache($event, [
            'Type' => 'set',
            'Key' => $event->key,
            'Value' => $this->formatValue($event),
            'Expiration' => $this->formatExpiration($event),
        ], 'width: 120px', 'Cache Written');
    }

    protected function sendCache(CacheEvent $event, array $data, string $headerStyle = '', string $label = ''): void
    {
        if (! $this->isEnabled('cache') || $this->shouldIgnore($event)) {
            return;
        }

        $payload = new TableV2Payload(
            $data,
            $headerStyle,
            'cache',
            $this->label ?: $label
        );

        (new LaraDumps())->send($payload);
    }

    private function shouldIgnore(mixed $event): bool
    {
        return Str::is([
            'illuminate:queue:restart',
            'framework/schedule*',
            'telescope:*',
        ], $event->key); // @phpstan-ignore-line
    }

    private function shouldHideValue(mixed $event): bool
    {
        return Str::is($this->hidden(), $event->key); // @phpstan-ignore-line
    }

    private function formatValue(mixed $event): mixed
    {
        return $this->shouldHideValue($event)
            ? '********'
            : $event->value; // @phpstan-ignore-line
    }

    private function formatExpiration(KeyWritten $event): int|null|float
    {
        return property_exists($event, 'seconds') // @phpstan-ignore-line
            ? $event->seconds
            : ($event->minutes ?? 0) * 60;
    }
}
