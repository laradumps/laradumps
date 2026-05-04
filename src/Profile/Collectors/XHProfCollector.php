<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

class XHProfCollector
{
    private bool $isAvailable = false;

    private bool $running = false;

    public function __construct(
        private readonly ProfileManager $manager
    ) {
        $this->checkAvailability();
    }

    private function checkAvailability(): void
    {
        if (! extension_loaded('xhprof') || ! function_exists('xhprof_enable')) {
            $this->isAvailable = false;

            return;
        }

        $this->isAvailable = true;
    }

    public function register(): void {}

    public function start(): void
    {
        if (! $this->isAvailable) {
            return;
        }

        try {
            xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
            $this->running = true;
        } catch (\Throwable) {
            $this->isAvailable = false;
        }
    }

    public function stop(): void
    {
        if (! $this->isAvailable || ! $this->running) {
            return;
        }

        try {
            $data = xhprof_disable();
            $this->running = false;

            if (! empty($data)) {
                $this->processData($data);
            }
        } catch (\Throwable) {
        }
    }

    private function processData(array $data): void
    {
        $appNamespace = rtrim(app()->getNamespace(), '\\').'\\';

        $children = [];
        $totalWt = 0;

        foreach ($data as $key => $stats) {
            if ($key === 'main()') {
                $totalWt = $stats['wt'] ?? 0;

                continue;
            }

            $sep = strpos($key, '==>');

            if ($sep === false) {
                continue;
            }

            $parent = substr($key, 0, $sep);
            $child = substr($key, $sep + 3);

            $children[$parent][$child] = $stats;
        }

        if ($totalWt <= 0) {
            return;
        }

        $totalMs = $totalWt / 1000;

        $this->manager->overrideTotalDuration($totalMs);

        $rootParentId = $this->manager->getCurrentParentId();

        $entryIdMap = [];
        $visited = [];

        $this->walkChildren(
            parent: 'main()',
            parentEntryId: $rootParentId,
            children: $children,
            appNamespace: $appNamespace,
            entryIdMap: $entryIdMap,
            visited: $visited,
            totalMs: $totalMs
        );
    }

    private function walkChildren(
        string $parent,
        ?string $parentEntryId,
        array &$children,
        string $appNamespace,
        array &$entryIdMap,
        array &$visited,
        float $totalMs,
        float $parentStartMs = 0.0
    ): void {
        if (! isset($children[$parent])) {
            return;
        }

        // Prevent infinite loops: track visited parent nodes only (not child occurrences)
        $visitKey = $parent.'@'.$parentEntryId;

        if (isset($visited[$visitKey])) {
            return;
        }

        $visited[$visitKey] = true;

        $offsetMs = 0.0;

        foreach ($children[$parent] as $child => $stats) {
            $wt = $stats['wt'] ?? 0;
            $durationMs = round($wt / 1000, 3);
            $startMs = round($parentStartMs + $offsetMs, 3);

            $isAppClass = $this->isAppClass($child, $appNamespace);

            $entryId = null;

            if ($isAppClass) {
                $shortName = $this->shortName($child);

                $entry = new ProfileEntry(
                    type: 'method',
                    name: "service({$shortName})",
                    startMs: $startMs,
                    durationMs: $durationMs,
                    parentId: $parentEntryId,
                    metadata: [
                        'function' => $child,
                        'source' => 'xhprof',
                        'calls' => $stats['ct'] ?? 1,
                        'memory' => $stats['mu'] ?? 0,
                    ],
                    origin: null
                );

                $this->manager->addEntry($entry);
                $entryId = $entry->id;
            }

            $this->walkChildren(
                parent: $child,
                parentEntryId: $entryId ?? $parentEntryId,
                children: $children,
                appNamespace: $appNamespace,
                entryIdMap: $entryIdMap,
                visited: $visited,
                totalMs: $totalMs,
                parentStartMs: $startMs
            );

            $offsetMs += $durationMs;
        }
    }

    private function isAppClass(string $fn, string $appNamespace): bool
    {
        $sep = strpos($fn, '::');

        if ($sep === false) {
            $sep = strpos($fn, '->');
        }

        if ($sep === false) {
            return false;
        }

        $className = substr($fn, 0, $sep);

        if (str_contains($className, '{') || str_contains($className, '@')) {
            return false;
        }

        return str_starts_with($className, $appNamespace);
    }

    private function shortName(string $fn): string
    {
        foreach (['->', '::'] as $sep) {
            $pos = strrpos($fn, $sep);

            if ($pos !== false) {
                return substr($fn, $pos + strlen($sep));
            }
        }

        return $fn;
    }
}
