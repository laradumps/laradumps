<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use LaraDumps\LaraDumps\Concerns\Traceable;
use LaraDumps\LaraDumps\Contracts\TraceableContract;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\QueriesPayload;

class QueryObserver implements TraceableContract
{
    use Traceable;

    private bool $enabled = false;

    private ?string $label = null;

    private array $trace = [];

    public function register(): void
    {
        DB::listen(function (QueryExecuted $query) {
            if (!$this->isEnabled()) {
                return;
            }

            $sqlQuery = str_replace(['%', '?'], ['%%', '\'%s\''], $query->sql);
            $bindings = array_map(function ($value) {
                if ($value instanceof \DateTime) {
                    return strval($value->format('Y-m-d H:i:s'));
                }

                return $value;
            }, $query->bindings);

            $sqlQuery = vsprintf($sqlQuery, $bindings);

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

    public function isEnabled(): bool
    {
        $this->trace   = array_slice($this->findSource(), 0, 5)[0] ?? [];

        if (!boolval(config('laradumps.send_queries'))) {
            return $this->enabled;
        }

        return true;
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
