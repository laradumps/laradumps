<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\{DB, Event};
use LaraDumps\LaraDumps\Payloads\QueriesPayload;
use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;

class QueryObserver
{
    private bool $enabled = false;

    private ?string $label = null;

    protected array $executedQueries = [];

    public function register(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            if (!$this->isEnabled()) {
                return;
            }

            try {
                $sql = DB::getQueryGrammar()
                    ->substituteBindingsIntoRawSql(
                        $query->sql,
                        $query->bindings
                    );

                if (!$this->shouldProcessQuery($sql)) {
                    return;
                }

                $duplicated = in_array($sql, $this->executedQueries);

                $this->executedQueries[] = $sql;

                if (!$duplicated && $this->onlyDuplicated()) {
                    return;
                }

                $request = $this->getRequest();

                $queries = [
                    'sql'            => $sql,
                    'duplicated'     => $duplicated,
                    'time'           => $query->time,
                    'database'       => $query->connection->getDatabaseName(),
                    'driver'         => $query->connection->getDriverName(),
                    'connectionName' => $query->connectionName,
                    'query'          => $query,
                    'uri'            => $request['uri'],
                    'method'         => $request['method'],
                    'origin'         => $request['origin'],
                    'argv'           => $request['argv'],
                ];

                $dumps   = new LaraDumps();
                $payload = new QueriesPayload($queries);

                $dumps->send($payload);

                if ($this->label) {
                    $dumps->label($this->label);
                }
            } catch (\Throwable) {
            }
        });
    }

    public function getRequest(): array
    {
        $request = request();

        if (null !== $qs = $request->getQueryString()) {
            $qs = '?' . $qs;
        }

        $origin = $request->server('argv') && $request->server('SCRIPT_NAME') === 'artisan' ? 'console' : 'http';

        return [
            'origin' => $origin,
            'argv'   => $request->server('argv'),
            'uri'    => str($request->getPathInfo() . $qs)->ltrim('/')->toString(),
            'method' => $request->getMethod(),
        ];
    }

    public function enable(?string $label = null): void
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

    public function isEnabled(): bool
    {
        if (!boolval(Config::get('observers.queries', false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.queries', false));
    }

    private function onlyDuplicated(): bool
    {
        return boolval(Config::get('queries.only_duplicated', false));
    }

    private function shouldProcessQuery(string $sql): bool
    {
        $sql = str($sql)->trim()->upper();

        $queryStatement = [
            'SELECT' => (bool) Config::get('queries.select', true),
            'INSERT' => (bool) Config::get('queries.insert', true),
            'UPDATE' => (bool) Config::get('queries.update', true),
            'DELETE' => (bool) Config::get('queries.delete', true),
        ];

        foreach ($queryStatement as $type => $isEnabled) {
            if (str($sql)->startsWith($type)) {
                return $isEnabled;
            }
        }

        return true;
    }
}
