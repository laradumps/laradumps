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

    private array $trace;

    public function register(): void
    {
        DB::listen(function (QueryExecuted $query) {
            if (!$this->enabled || !$this->isEnabled()) {
                return;
            }

            $sqlQuery = str_replace(['?'], ['\'%s\''], $query->sql);
            $sqlQuery = vsprintf($sqlQuery, $query->bindings);

            $queries = [
                'sql'            => $sqlQuery,
                'time'           => $query->time,
                'database'       => $query->connection->getDatabaseName(),
                'connectionName' => $query->connectionName,
                'query'          => $query,
            ];

            $dumps = new LaraDumps(backtrace: $this->trace);

            $dumps->send(new QueriesPayload($queries));

            if ($this->label) {
                $dumps->label($this->label);
            }
        });
    }

    public function enable(string $label = null): void
    {
        $this->label = $label;
        if (!$this->isEnabled()) {
            ds('🤐 It looks like you tried to dump SQL with "send_queries disabled". \nTo protect your information, listening to SQL Queries is disabled by default. \nChange  "send_queries disabled" = true in the config/laradumps.php file.');

            return;
        }

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
        return (bool) config('laradumps.send_queries');
    }
}
