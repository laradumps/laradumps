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
        DB::listen(function (QueryExecuted $query) {
            $minimumTimeInMs = (float) Config::get('slow_queries.threshold_in_ms');

            if (($query->time * 1000) >= $minimumTimeInMs) {
                $sqlQuery = str_replace(['%', '?'], ['%%', '\'%s\''], $query->sql);
                $bindings = array_map(function ($value) {
                    if ($value instanceof \DateTime) {
                        return strval($value->format('Y-m-d H:i:s'));
                    }

                    return $value;
                }, $query->bindings);

                $sqlQuery = vsprintf($sqlQuery, $bindings);

                $queries = [
                    'sql'            => $sqlQuery,
                    'time'           => $query->time,
                    'database'       => $query->connection->getDatabaseName(),
                    'connectionName' => $query->connectionName,
                    'query'          => $query,
                ];

                $dumps = new LaraDumps();

                $payload = new QueriesPayload($queries);

                $dumps->send($payload);

                $dumps->toScreen('Slow Queries');
            }
        });
    }

    public function isEnabled(): bool
    {
        if (app()->bound('db')) {
            collect(DB::getConnections())->each(function ($connection) {
                $connection->enableQueryLog();
            });
        }

        return boolval(Config::get('observers.slow_queries', false));
    }
}
