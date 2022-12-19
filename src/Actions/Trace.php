<?php

namespace LaraDumps\LaraDumps\Actions;

class Trace
{
    protected array $backtraceExcludePaths = [
        '/vendor/laravel/framework/src/Illuminate/Support',
        '/vendor/laravel/framework/src/Illuminate/Database',
        '/vendor/laravel/framework/src/Illuminate/Events',
        '/vendor/laravel/framework/src/Illuminate/Log',
        '/vendor/barryvdh',
        '/vendor/symfony',
        '/artisan',
        '/vendor/livewire',
        '/packages/laradumps',
        '/vendor/laradumps',
    ];

    private array $trace;

    public function __construct()
    {
        $this->trace = self::mapTrace();
    }

    private function mapTrace(): array
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);

        $sources = [];

        foreach ($stack as $trace) {
            $sources[] = $this->parseTrace($trace);
        }

        return array_filter($sources);
    }

    public function toArray(): array
    {
        return array_slice($this->trace, 0, 5)[0] ?? [];
    }

    public static function findSource(): Trace
    {
        return new self();
    }

    protected function parseTrace(array $trace): array
    {
        if (
            isset($trace['class']) && isset($trace['file']) &&
            !$this->fileIsInExcludedPath($trace['file'])
        ) {
            return $trace;
        }

        return [];
    }

    protected function fileIsInExcludedPath(string $file): bool
    {
        $normalizedPath = str_replace('\\', '/', $file);

        foreach ($this->backtraceExcludePaths as $excludedPath) {
            if (str_contains($normalizedPath, $excludedPath)) {
                return true;
            }
        }

        return false;
    }
}
