<?php

namespace LaraDumps\LaraDumps\Profile;

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

    public function __construct()
    {
        $this->stack = new ProfileStack();
        $this->maxEntries = intval(Config::get('profile.max_entries', 1000));
        $this->loadCaptureConfig();
    }

    private function loadCaptureConfig(): void
    {
        $this->captureConfig = [
            'app' => boolval(Config::get('profile.capture.app', true)),
            'events' => boolval(Config::get('profile.capture.events', true)),
            'queries' => boolval(Config::get('profile.capture.queries', true)),
            'eloquent' => boolval(Config::get('profile.capture.eloquent', true)),
            'views' => boolval(Config::get('profile.capture.views', true)),
            'controller' => boolval(Config::get('profile.capture.controller', true)),
            'http' => boolval(Config::get('profile.capture.http', true)),
            'cache' => boolval(Config::get('profile.capture.cache', true)),
            'jobs' => boolval(Config::get('profile.capture.jobs', true)),
            'method' => boolval(Config::get('profile.capture.method', true)),
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

        // Only capture wall-clock end time if it hasn't already been overridden
        // (e.g. by XdebugCollector::overrideTotalDuration to avoid inflated times).
        if ($this->endTime === null) {
            $this->endTime = microtime(true) * 1000;
        }

        foreach ($this->entries as $entry) {
            if ($entry->id === $this->rootEntryId && $entry->durationMs === null) {
                $entry->stop($this->endTime - $this->startTime);
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

    public function pushContext(string $entryId): void
    {
        $this->stack->push($entryId);
    }

    public function popContext(): ?string
    {
        return $this->stack->pop();
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
        $totalDuration = $this->endTime !== null
            ? $this->endTime - $this->startTime
            : $this->getElapsedMs();

        return [
            'profile_id' => uniqid('profile_', true),
            'label' => $this->label,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'total_duration_ms' => round($totalDuration, 3),
            'entries' => array_map(fn (ProfileEntry $e) => $e->toArray(), $this->entries),
            'summary' => $this->buildSummary(),
        ];
    }

    private function buildSummary(): array
    {
        $summary = [
            'total_entries' => count($this->entries),
            'by_type' => [],
        ];

        // Build a set of entry IDs per type to detect parent-child nesting within the same type.
        // For types where entries can nest (e.g. 'method'), we only count top-level entries
        // in the duration total to avoid double-counting parent + child durations.
        $idsByType = [];

        foreach ($this->entries as $entry) {
            $idsByType[$entry->type][$entry->id] = true;
        }

        foreach ($this->entries as $entry) {
            if (! isset($summary['by_type'][$entry->type])) {
                $summary['by_type'][$entry->type] = [
                    'count' => 0,
                    'total_duration_ms' => 0,
                ];
            }

            $summary['by_type'][$entry->type]['count']++;

            if ($entry->durationMs !== null) {
                $parentIsSameType = $entry->parentId !== null
                    && isset($idsByType[$entry->type][$entry->parentId]);

                if (! $parentIsSameType) {
                    $summary['by_type'][$entry->type]['total_duration_ms'] += $entry->durationMs;
                }
            }
        }

        foreach ($summary['by_type'] as $type => $data) {
            $summary['by_type'][$type]['total_duration_ms'] = round($data['total_duration_ms'], 3);
        }

        return $summary;
    }

    public function overrideTotalDuration(float $durationMs): void
    {
        if ($this->rootEntryId === null) {
            return;
        }

        foreach ($this->entries as $entry) {
            if ($entry->id === $this->rootEntryId) {
                $entry->durationMs = round($durationMs, 3);
                break;
            }
        }

        $this->endTime = $this->startTime + $durationMs;
    }

    public function captureBacktrace(int $limit = 10): ?array
    {
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
                'method' => $frame['function'] ?? null,
                'file' => $file,
                'line' => $frame['line'] ?? null,
            ];
        }

        return null;
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
    }

    public function measure(string $name, callable $callback, string $type = 'app', array $metadata = []): mixed
    {
        if (! $this->isActive) {
            return $callback();
        }

        $startMs = $this->getElapsedMs();

        $entry = new ProfileEntry(
            type: $type,
            name: $name,
            startMs: $startMs,
            parentId: $this->getCurrentParentId(),
            metadata: $metadata,
            origin: $this->captureBacktrace()
        );

        $this->addEntry($entry);
        $this->pushContext($entry->id);

        try {
            $result = $callback();
        } finally {
            $entry->stop($this->getElapsedMs());
            $this->popContext();
        }

        return $result;
    }
}
