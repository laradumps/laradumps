<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\{DB, Event};
use LaraDumps\LaraDumps\Payloads\QueriesPayload;
use LaraDumps\LaraDumpsCore\LaraDumps;
use Spatie\Backtrace\Backtrace;

class QueryObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen(QueryExecuted::class, fn (QueryExecuted $query) => $this->handle($query));
    }

    public function enable(string $label = ''): void
    {
        $this->label = $label;
        DB::enableQueryLog();
        $this->enabled = true;
    }

    public function disable(): void
    {
        DB::disableQueryLog();
        $this->enabled = false;
    }

    private function handle(QueryExecuted $query): void
    {
        if (! $this->isEnabled('queries')) {
            return;
        }

        try {
            $sql = DB::getQueryGrammar()->substituteBindingsIntoRawSql($query->sql, $query->bindings);

            $queries = $this->buildQueryPayload($query, $sql);
            $frame = app(LaraDumps::class)->parseFrame(Backtrace::create());

            $payload = new QueriesPayload($queries);
            $payload->setFrame($frame);

            $dumper = new LaraDumps();
            $dumper->send($payload, withFrame: false);

            if ($this->label) {
                $dumper->label($this->label);
            }
        } catch (\Throwable) {
        }
    }

    private function buildQueryPayload(QueryExecuted $query, string $sql): array
    {
        $request = $this->resolveRequestContext();

        $query->sql = $sql;

        return [
            'time' => $query->time,
            'database' => $query->connection->getDatabaseName(),
            'driver' => $query->connection->getDriverName(),
            'connectionName' => $query->connectionName,
            'query' => $query,
            'uri' => $request['uri'],
            'method' => $request['method'],
            'origin' => $request['origin'],
            'argv' => $request['argv'],
        ];
    }

    private function resolveRequestContext(): array
    {
        $request = request();

        $queryString = $request->getQueryString();
        $uri = str($request->getPathInfo().($queryString ? "?$queryString" : ''))
            ->ltrim('/')
            ->toString();

        $origin = ($request->server('argv') && $request->server('SCRIPT_NAME') === 'artisan')
            ? 'console'
            : 'http';

        return [
            'origin' => $origin,
            'argv' => $request->server('argv'),
            'uri' => $uri,
            'method' => $request->getMethod(),
        ];
    }
}
