<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\{DB, Event};
use LaraDumps\LaraDumps\Payloads\QueriesPayload;
use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;

class SlowQueryObserver
{
    public function register(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            if (!$this->isEnabled()) {
                return;
            }

            /** @var float $minimumTimeInMs */
            $minimumTimeInMs = Config::get('slow_queries.threshold_in_ms', 500);

            if ($query->time >= $minimumTimeInMs) {
                $toSql = DB::getQueryGrammar()
                    ->substituteBindingsIntoRawSql(
                        $query->sql,
                        $query->bindings
                    );

                $queries = [
                    'sql'            => $toSql,
                    'time'           => $query->time,
                    'database'       => $query->connection->getDatabaseName(),
                    'connectionName' => $query->connectionName,
                    'query'          => $query,
                ];

                $dumps = new LaraDumps();

                $payload = new QueriesPayload($queries, screen: 'Slow Queries');

                $dumps->send($payload);
            }
        });
    }

    public function isEnabled(): bool
    {
        $enabled = boolval(Config::get('observers.slow_queries', false));

        if ($enabled && app()->bound('db')) {
            collect(DB::getConnections())->each(fn ($connection) => $connection->enableQueryLog());
        }

        return $enabled;
    }
}
