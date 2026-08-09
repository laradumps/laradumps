<?php

namespace LaraDumps\LaraDumps\Profile;

use LaraDumps\LaraDumps\Profile\Tracing\ProfileTracer;
use LaraDumps\LaraDumpsCore\Actions\Config;

class ProfileManager
{
    private bool $isActive = false;

    private float $startTime = 0;

    private ?float $endTime = null;

    private ?string $label = null;

    private array $entries = [];

    private ProfileStack $stack;

    private int $maxEntries;

    private array $captureConfig = [];

    private ?string $rootEntryId = null;

    private ?string $contextEntryId = null;

    private float $overheadMs = 0.0;

    private ?ProfileTracer $tracer = null;

    public function __construct()
    {
        $this->stack = new ProfileStack();
        $this->maxEntries = intval(Config::get('profiler.max_entries', 1000));
        $this->loadCaptureConfig();
    }

    public function setTracer(?ProfileTracer $tracer): void
    {
        $this->tracer = $tracer;
    }

    public function tracer(): ?ProfileTracer
    {
        return $this->tracer;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getRootEntryId(): ?string
    {
        return $this->rootEntryId;
    }

    public function setContextEntryId(?string $contextEntryId): void
    {
        $this->contextEntryId = $contextEntryId;
    }

    public function getContextEntryId(): ?string
    {
        return $this->contextEntryId;
    }

    private function loadCaptureConfig(): void
    {
        $this->captureConfig = [
            'app' => boolval(Config::get('profiler.capture.app', true)),
            'events' => boolval(Config::get('profiler.capture.events', true)),
            'queries' => boolval(Config::get('profiler.capture.queries', true)),
            'eloquent' => boolval(Config::get('profiler.capture.eloquent', true)),
            'views' => boolval(Config::get('profiler.capture.views', true)),
            'controller' => boolval(Config::get('profiler.capture.controller', true)),
            'http' => boolval(Config::get('profiler.capture.http', true)),
            'cache' => boolval(Config::get('profiler.capture.cache', true)),
            'jobs' => boolval(Config::get('profiler.capture.jobs', true)),
            'method' => boolval(Config::get('profiler.capture.method', true)),
        ];
    }

    public function start(?string $label = null): void
    {
        $this->isActive = true;
        $this->startTime = microtime(true) * 1000;
        $this->endTime = null;
        $this->label = $label ?? 'Profile at '.date('Y-m-d H:i:s');
        $this->entries = [];
        $this->stack->clear();
        $this->rootEntryId = null;
        $this->contextEntryId = null;
        $this->overheadMs = 0.0;

        $rootEntry = new ProfileEntry(
            type: 'app',
            name: 'app(start)',
            startMs: 0,
            parentId: null,
            metadata: [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ]
        );

        $this->rootEntryId = $rootEntry->id;
        $this->entries[] = $rootEntry;
        $this->stack->push($rootEntry->id);
    }

    public function stop(): array
    {
        if (! $this->isActive) {
            return [];
        }

        if ($this->endTime === null) {
            $this->endTime = microtime(true) * 1000;
        }

        foreach ($this->entries as $entry) {
            if ($entry->id === $this->rootEntryId && $entry->durationMs === null) {
                $entry->stop($this->adjustedTotalMs());
            }
        }

        $this->isActive = false;
        $this->stack->clear();

        return $this->getProfileData();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function shouldCapture(string $type): bool
    {
        return $this->captureConfig[$type] ?? true;
    }

    public function addEntry(ProfileEntry $entry): void
    {
        if (! $this->isActive) {
            return;
        }

        if (count($this->entries) >= $this->maxEntries) {
            return;
        }

        if ($entry->parentId === null && $this->stack->current()) {
            $entry->parentId = $this->stack->current();
        }

        $this->entries[] = $entry;
    }

    public function getCurrentParentId(): ?string
    {
        return $this->stack->current();
    }

    public function getElapsedMs(): float
    {
        if (! $this->isActive) {
            return 0;
        }

        return (microtime(true) * 1000) - $this->startTime;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    public function getProfileData(): array
    {
        $wallDuration = $this->endTime !== null
            ? $this->endTime - $this->startTime
            : $this->getElapsedMs();

        $totalDuration = $this->adjustedTotalMs();

        $selfTimes = $this->computeSelfTimes();

        foreach ($this->entries as $entry) {
            $entry->selfDurationMs = $selfTimes[$entry->id] ?? null;
        }

        return [
            'profile_id' => uniqid('profile_', true),
            'label' => $this->label,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'total_duration_ms' => round($totalDuration, 3),
            'wall_duration_ms' => round($wallDuration, 3),
            'overhead_ms' => round($this->overheadMs, 3),
            'entries' => array_map(fn (ProfileEntry $e) => $e->toArray(), $this->entries),
            'summary' => $this->buildSummary($selfTimes),
        ];
    }

    private function adjustedTotalMs(): float
    {
        $wall = $this->endTime !== null
            ? $this->endTime - $this->startTime
            : $this->getElapsedMs();

        return max(0.0, $wall - $this->overheadMs);
    }

    public function addOverheadMs(float $ms): void
    {
        if ($ms > 0) {
            $this->overheadMs += $ms;
        }
    }

    public function getOverheadMs(): float
    {
        return $this->overheadMs;
    }

    private function computeSelfTimes(): array
    {
        $entriesById = [];

        foreach ($this->entries as $entry) {
            $entriesById[$entry->id] = $entry;
        }

        $childrenSum = [];

        foreach ($this->entries as $entry) {
            if ($entry->durationMs === null || $entry->parentId === null) {
                continue;
            }

            if (! isset($entriesById[$entry->parentId])) {
                continue;
            }

            $childrenSum[$entry->parentId] = ($childrenSum[$entry->parentId] ?? 0.0) + $entry->durationMs;
        }

        $selfTimes = [];

        foreach ($this->entries as $entry) {
            if ($entry->durationMs === null) {
                $selfTimes[$entry->id] = null;

                continue;
            }

            $selfTimes[$entry->id] = max(0.0, $entry->durationMs - ($childrenSum[$entry->id] ?? 0.0));
        }

        return $selfTimes;
    }

    private function buildSummary(array $selfTimes): array
    {
        $summary = [
            'total_entries' => count($this->entries),
            'by_type' => [],
        ];

        foreach ($this->entries as $entry) {
            if (! isset($summary['by_type'][$entry->type])) {
                $summary['by_type'][$entry->type] = [
                    'count' => 0,
                    'total_duration_ms' => 0,
                ];
            }

            $summary['by_type'][$entry->type]['count']++;

            $selfMs = $selfTimes[$entry->id] ?? null;

            if ($selfMs !== null) {
                $summary['by_type'][$entry->type]['total_duration_ms'] += $selfMs;
            }
        }

        foreach ($summary['by_type'] as $type => $data) {
            $summary['by_type'][$type]['total_duration_ms'] = round($data['total_duration_ms'], 3);
        }

        return $summary;
    }

    public function captureBacktrace(int $limit = 10): ?array
    {
        $start = microtime(true);

        try {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit + 5);

            foreach ($trace as $frame) {
                $file = $frame['file'] ?? '';

                if (str_contains($file, 'laradumps')) {
                    continue;
                }

                if (str_contains($file, 'vendor/')) {
                    continue;
                }

                return [
                    'class' => $frame['class'] ?? null,
                    'method' => $frame['function'],
                    'file' => $file,
                    'line' => $frame['line'] ?? null,
                ];
            }

            return null;
        } finally {
            $this->overheadMs += (microtime(true) - $start) * 1000;
        }
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function clear(): void
    {
        $this->isActive = false;
        $this->startTime = 0;
        $this->endTime = null;
        $this->label = null;
        $this->entries = [];
        $this->stack->clear();
        $this->rootEntryId = null;
        $this->contextEntryId = null;
        $this->overheadMs = 0.0;
    }

    public function measure(string $name, callable $callback, string $type = 'app', array $metadata = []): mixed
    {
        if (! $this->isActive || $this->tracer === null) {
            return $callback();
        }

        $span = $this->tracer->beginScopedSpan($type, $name, $metadata, $this->captureBacktrace());

        try {
            return $callback();
        } finally {
            $span->end();
        }
    }
}
