<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\QueriesPayload;

class QueryObserver
{
    private bool $enabled = false;

    private ?string $label = null;

    private array $trace = [];

    protected array $backtraceExcludePaths = [
        '/vendor/laravel/framework/src/Illuminate/Support',
        '/vendor/laravel/framework/src/Illuminate/Database',
        '/vendor/laravel/framework/src/Illuminate/Events',
        '/vendor/barryvdh',
        '/vendor/symfony',
        '/artisan',
        '/vendor/livewire',
        '/packages/laradumps',
        '/vendor/laradumps',
    ];

    public function register(): void
    {
        DB::listen(function (QueryExecuted $query) {
            if (!$this->isEnabled()) {
                return;
            }

            $sqlQuery = str_replace(['?'], ['\'%s\''], $query->sql);
            $sqlQuery = vsprintf($sqlQuery, $query->bindings);

            if (str_contains($sqlQuery, 'telescope')) {
                return;
            }

            $queries = [
                'sql'                       => $sqlQuery,
                'time'                      => $query->time,
                'database'                  => $query->connection->getDatabaseName(),
                'connectionName'            => $query->connectionName,
                'query'                     => $query,
                'formatted'                 => boolval(config('laradumps.send_queries.formatted', false)),
                'showConnectionInformation' => boolval(config('laradumps.send_queries.show_connection_information', false)),
            ];

            $dumps = new LaraDumps(trace: $this->trace);

            $dumps->send(new QueriesPayload($queries));

            if ($this->label) {
                $dumps->label($this->label);
            }
        });
    }

    public function enable(string $label = null): void
    {
        $this->label = $label;

        DB::enableQueryLog();

        $this->enabled    = true;
    }

    public function disable(): void
    {
        DB::disableQueryLog();

        $this->enabled    = false;
    }

    public function setTrace(array $trace): array
    {
        return $this->trace = $trace;
    }

    public function isEnabled(): bool
    {
        $this->trace   = array_slice($this->findSource(), 0, 5)[0] ?? [];

        if (!boolval(config('laradumps.send_queries'))) {
            return $this->enabled;
        }

        return true;
    }

    protected function findSource(): array
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);

        $sources = [];

        foreach ($stack as $trace) {
            $sources[] = $this->parseTrace($trace);
        }

        return array_filter($sources);
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
