<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
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
        DB::listen(function (QueryExecuted $query) {
            if (!$this->isEnabled()) {
                return;
            }

            try {
                $sql = DB::getQueryGrammar()
                    ->substituteBindingsIntoRawSql(
                        $query->sql,
                        $query->bindings
                    );

                $duplicated = in_array($sql, $this->executedQueries);

                $this->executedQueries[] = $sql;

                if (!$duplicated && $this->onlyDuplicated()) {
                    return;
                }

                $queries = [
                    'sql'            => $sql,
                    'duplicated'     => $duplicated,
                    'time'           => $query->time,
                    'database'       => $query->connection->getDatabaseName(),
                    'connectionName' => $query->connectionName,
                    'query'          => $query,
                ];

                $dumps   = new LaraDumps();
                $payload = new QueriesPayload($queries);

                $dumps->send($payload);

                if ($this->label) {
                    $dumps->label($this->label);
                }

                $dumps->toScreen('Queries');
            } catch (\Throwable) {
            }
        });
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

    private function onlyDuplicated()
    {
        return Config::get('queries.only_duplicated', false);
    }
}
