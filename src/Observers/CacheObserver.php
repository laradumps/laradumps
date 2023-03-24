<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Cache\Events\{CacheEvent, CacheHit, CacheMissed, KeyForgotten, KeyWritten};
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\TableV2Payload;

class CacheObserver
{
    use Traceable;

    protected ?string $label = null;

    protected array $hidden = [];

    private bool $enabled = false;

    public function register(): void
    {
        Event::listen(CacheHit::class, function (CacheHit $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type'  => 'hit',
                'Key'   => $event->key,
                'Value' => $this->formatValue($event),
            ], 'width: 120px');
        });

        Event::listen(CacheMissed::class, function (CacheMissed $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type' => 'missed',
                'Key'  => $event->key,
            ], 'width: 120px');
        });

        Event::listen(KeyForgotten::class, function (KeyForgotten $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type' => 'forget',
                'Key'  => $event->key,
            ], 'width: 120px');
        });

        Event::listen(KeyWritten::class, function (KeyWritten $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $this->sendCache($event, [
                'Type'       => 'set',
                'Key'        => $event->key,
                'Value'      => $this->formatValue($event),
                'Expiration' => $this->formatExpiration($event),
            ], 'width: 120px');
        });
    }

    protected function sendCache(CacheEvent $event, array $data, string $headerStyle = ''): void
    {
        if (!$this->isEnabled() || $this->shouldIgnore($event)) {
            return;
        }

        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send(
            new TableV2Payload($data, $headerStyle)
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

        if (!boolval(Config::get('send_cache'))) {
            return $this->enabled;
        }

        return false;
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
