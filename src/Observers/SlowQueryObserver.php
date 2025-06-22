<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\{DB, Event};
use LaraDumps\LaraDumps\Payloads\QueriesPayload;
use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;

class SlowQueryObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen(QueryExecuted::class, fn (QueryExecuted $event) => $this->handle($event));
    }

    public function isEnabled(string $key): bool
    {
        $enabled = (bool) Config::get('observers.slow_queries', false);

        if ($enabled && app()->bound('db')) {
            collect(DB::getConnections())->each(fn ($connection) => $connection->enableQueryLog());
        }

        return $enabled;
    }

    private function handle(QueryExecuted $event): void
    {
        if (! $this->isEnabled('slow_queries')) {
            return;
        }

        if (! $this->isSlow($event->time)) {
            return;
        }

        $sql = $this->resolveFullSql($event);

        $queries = $this->buildQueryMetadata($event, $sql);

        $payload = new QueriesPayload($queries, screen: 'slow queries');

        (new LaraDumps())->send($payload);
    }

    private function isSlow(float $time): bool
    {
        $threshold = (float) Config::get('slow_queries.threshold_in_ms', 500); // @phpstan-ignore-line

        return $time >= $threshold;
    }

    private function resolveFullSql(QueryExecuted $event): string
    {
        return DB::getQueryGrammar()
            ->substituteBindingsIntoRawSql($event->sql, $event->bindings);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQueryMetadata(QueryExecuted $event, string $sql): array
    {
        $event->sql = $sql;

        return [
            'time' => $event->time,
            'database' => $event->connection->getDatabaseName(),
            'driver' => $event->connection->getDriverName(),
            'query' => $event,
        ];
    }
}
