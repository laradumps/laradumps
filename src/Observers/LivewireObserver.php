<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{Event, Vite};
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LivewirePayload;
use Livewire\Mechanisms\HandleComponents\HandleComponents;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

use function Livewire\on;

class LivewireObserver extends BaseObserver
{
    public const SELECT_URI = '_laradumps/livewire/select';

    public const SELECTED_KEY = 'laradumps:livewire:selected';

    private const MAX_COMPONENTS = 100;

    private const MAX_QUERIES = 50;

    private const MAX_STATE_BYTES = 100_000;

    private static ?string $pickerJs = null;

    /** @var array<string, list<array<string, mixed>>> */
    private array $queries = [];

    /** @var array<string, list<array{0: string, 1: float}>> */
    private array $profile = [];

    /** @var array<string, true> */
    private array $pinned = [];

    public function register(): void
    {
        if (! class_exists(HandleComponents::class)) {
            return;
        }

        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            $component = end(HandleComponents::$componentStack);

            if (! $component || ! $this->isEnabled('livewire')) {
                return;
            }

            $id = $component->getId();

            if ($this->hasRoom($this->queries, $id) && count($this->queries[$id] ?? []) < self::MAX_QUERIES) {
                $this->queries[$id][] = [
                    'sql' => rescue(fn () => $query->toRawSql(), $query->sql, false),
                    'time' => $query->time,
                    'connectionName' => $query->connectionName,
                    'driver' => $query->connection->getDriverName(),
                ];
            }
        });

        on('profile', function (string $method, string $id, array $measurement) {
            if ($this->isEnabled('livewire') && $this->hasRoom($this->profile, $id)) {
                $this->profile[$id][] = [$method, round(($measurement[1] - $measurement[0]) * 1000, 2)];
            }
        });

        on('flush-state', function () {
            $this->reset();
            $this->disable();
        });

        Event::listen(RequestHandled::class, function (RequestHandled $event) {
            if (! $this->isEnabled('livewire')) {
                $this->reset();

                return;
            }

            $this->injectPicker($event->request, $event->response);

            app()->terminating(fn () => $this->flush($event->request, $event->response));
        });
    }

    public function pin(string $name): void
    {
        $this->pinned[$name] = true;
        $this->enable();
    }

    /** @return list<string> */
    public function selected(): array
    {
        return array_values(array_filter((array) rescue(fn () => cache()->get(self::SELECTED_KEY), [], false), 'is_string'));
    }

    public function flush(Request $request, SymfonyResponse $response): void
    {
        try {
            $wanted = array_flip([...$this->selected(), ...array_keys($this->pinned)]);

            if (! $wanted) {
                return;
            }

            $content = $response->getContent();
            $content = is_string($content) ? $content : '';

            $items = $request->hasHeader('X-Livewire')
                ? $this->collect((array) $request->input('components', []), json_decode($content, true)['components'] ?? [])
                : $this->collect([], [], $content);

            $items = array_values(array_filter($items, fn (array $item) => isset($wanted[$item['name']])));

            if ($items) {
                app(LaraDumps::class)->send(new LivewirePayload(['components' => $items]), withFrame: false);
            }
        } catch (Throwable $e) {
            report($e);
        } finally {
            $this->reset();
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $requestComponents
     * @param  array<int, array<string, mixed>>  $responseComponents
     * @return list<array<string, mixed>>
     */
    public function collect(array $requestComponents, array $responseComponents, string $html = ''): array
    {
        $items = [];

        foreach ($requestComponents as $i => $in) {
            $out = $responseComponents[$i] ?? null;

            if (! is_array($out) || ! isset($out['snapshot'], $in['snapshot'])) {
                continue;
            }

            $snapshot = json_decode($out['snapshot'], true);
            $before = (array) self::unwrap(json_decode($in['snapshot'], true)['data'] ?? []);
            $after = (array) self::unwrap($snapshot['data'] ?? []);
            $item = $this->item($snapshot, strlen($out['snapshot']), 'update', $after);
            $changes = [];

            foreach ($after as $key => $value) {
                if (! array_key_exists($key, $before) || $before[$key] !== $value) {
                    $changes[$key] = [$before[$key] ?? null, $value];
                }
            }

            $items[] = [
                ...$item,
                'updates' => $in['updates'] ?? [],
                'calls' => array_map(fn ($call) => ['method' => $call['method'] ?? '', 'params' => $call['params'] ?? []], $in['calls'] ?? []),
                'changes' => $changes,
                'dispatches' => $out['effects']['dispatches'] ?? [],
            ];

            $html .= $out['effects']['html'] ?? '';
        }

        $seen = array_flip(array_column($items, 'id'));

        foreach ($this->snapshotsIn($html) as $raw) {
            $snapshot = json_decode($raw, true);

            if (is_array($snapshot) && ! isset($seen[$snapshot['memo']['id'] ?? ''])) {
                $items[] = $this->item($snapshot, strlen($raw), 'mount', self::unwrap($snapshot['data'] ?? []));
            }
        }

        $items = array_slice($items, 0, self::MAX_COMPONENTS);
        $names = array_column($items, 'name', 'id');

        foreach ($items as &$item) {
            $item['profile'] = $this->profileOf($item, $names);
        }

        return $items;
    }

    public static function unwrap(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (count($value) === 2 && array_is_list($value) && is_array($value[1]) && isset($value[1]['s'])) {
            [$value, $meta] = $value;

            if ($value === null && isset($meta['class'])) {
                $key = $meta['key'] ?? (isset($meta['keys']) ? implode(',', (array) $meta['keys']) : null);

                return ($meta['modelClass'] ?? $meta['class']).($key !== null ? '#'.$key : '');
            }
        }

        return is_array($value) ? array_map(self::unwrap(...), $value) : $value;
    }

    public function select(Request $request): SymfonyResponse
    {
        abort_unless($this->isEnabled('livewire'), 404);

        $names = array_values(array_slice(array_filter((array) $request->input('names', []), fn ($name) => is_string($name) && strlen($name) <= 200), 0, 50));

        $names ? cache()->forever(self::SELECTED_KEY, $names) : cache()->forget(self::SELECTED_KEY);

        app(LaraDumps::class)->send(new LivewirePayload(['focus' => $names]), withFrame: false);

        return response()->noContent();
    }

    /** @return array<string, mixed> */
    private function item(array $snapshot, int $size, string $action, mixed $state): array
    {
        $id = (string) ($snapshot['memo']['id'] ?? '');

        return [
            'id' => $id,
            'name' => (string) ($snapshot['memo']['name'] ?? ''),
            'action' => $action,
            'state' => $size <= self::MAX_STATE_BYTES ? $state : null,
            'errors' => $snapshot['memo']['errors'] ?? [],
            'queries' => $this->queries[$id] ?? [],
            'size' => $size,
        ];
    }

    /**
     * @param  array<string, string>  $names
     * @return list<array{kind: string, label: string, ms: float}>
     */
    private function profileOf(array $item, array $names): array
    {
        return array_map(function (array $entry) use ($item, $names) {
            [$key, $ms] = $entry;

            if (preg_match('/^call(\d+)$/', $key, $match)) {
                $call = $item['calls'][$match[1]] ?? ['method' => 'call', 'params' => []];

                return $call['method'] === '__dispatch'
                    ? ['kind' => 'listener', 'label' => (string) ($call['params'][0] ?? '__dispatch'), 'ms' => $ms]
                    : ['kind' => 'call', 'label' => $call['method'], 'ms' => $ms];
            }

            if (str_starts_with($key, 'child:')) {
                return ['kind' => 'child', 'label' => $names[substr($key, 6)] ?? 'child', 'ms' => $ms];
            }

            return ['kind' => $key, 'label' => $key, 'ms' => $ms];
        }, $this->profile[$item['id']] ?? []);
    }

    /** @return list<string> */
    private function snapshotsIn(string $html): array
    {
        if (! str_contains($html, 'wire:snapshot="')) {
            return [];
        }

        preg_match_all('/wire:snapshot="([^"]*)"/', $html, $matches);

        return array_map(fn ($raw) => htmlspecialchars_decode($raw, ENT_QUOTES), $matches[1]);
    }

    private function hasRoom(array $bag, string $id): bool
    {
        return isset($bag[$id]) || count($bag) < self::MAX_COMPONENTS;
    }

    private function injectPicker(Request $request, SymfonyResponse $response): void
    {
        if (! config('app.debug') || $request->hasHeader('X-Livewire') || ! $response instanceof Response) {
            return;
        }

        $html = $response->getContent();

        if (! is_string($html)
            || ! str_contains((string) $response->headers->get('Content-Type', 'text/html'), 'html')
            || ! str_contains($html, 'wire:snapshot="')
            || ($at = strripos($html, '</body>')) === false) {
            return;
        }

        $nonce = Vite::cspNonce();

        $script = sprintf(
            '<script%s data-endpoint="%s" data-selected="%s">%s</script>',
            $nonce ? ' nonce="'.e($nonce).'"' : '',
            e(url(self::SELECT_URI)),
            e((string) json_encode($this->selected())),
            self::$pickerJs ??= (string) file_get_contents(__DIR__.'/../../resources/js/livewire-picker.js'),
        );

        $response->setContent(substr_replace($html, $script, $at, 0));
    }

    private function reset(): void
    {
        $this->queries = [];
        $this->profile = [];
        $this->pinned = [];
    }
}
