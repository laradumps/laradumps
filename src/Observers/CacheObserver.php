<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Cache\Events\{CacheEvent, CacheHit, CacheMissed, KeyForgotten, KeyWritten};
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\TableV2Payload;

class CacheObserver extends BaseObserver
{
    protected string $label = '';

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
            'Key' => $event->key,
            'Value' => $this->formatValue($event),
        ], 'width: 120px', 'Hit');
    }

    public function handleMissed(CacheMissed $event): void
    {
        $this->sendCache($event, [
            'Key' => $event->key,
        ], 'width: 120px', 'Missed');
    }

    public function handleForgotten(KeyForgotten $event): void
    {
        $this->sendCache($event, [
            'Key' => $event->key,
        ], 'width: 120px', 'Forget');
    }

    public function handleWritten(KeyWritten $event): void
    {
        $this->sendCache($event, [
            'Key' => $event->key,
            'Value' => $this->formatValue($event),
            'Expiration' => $this->formatExpiration($event),
        ], 'width: 120px', 'Set');
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

        $laradumps = new LaraDumps();

        $laradumps->send($payload);

        match ($label) {
            'Hit' => $laradumps->blue(),
            'Missed' => $laradumps->warning(),
            'Forget' => $laradumps->red(),
            default => $laradumps->green(),
        };
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
