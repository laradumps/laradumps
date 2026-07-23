<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\{DB, Event};
use LaraDumps\LaraDumps\Profile\ProfileManager;

class QueryCollector
{
    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            $this->handle($query);
        });
    }

    private function handle(QueryExecuted $query): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('queries')) {
            return;
        }

        if ($this->isExplainQuery($query->sql)) {
            return;
        }

        $sql = $this->formatSql($query);
        $name = $this->buildName($sql);

        $metadata = [
            'sql' => $sql,
            'bindings' => $query->bindings,
            'connection' => $query->connectionName,
            'database' => $query->connection->getDatabaseName(),
        ];

        $origin = $this->manager->captureBacktrace();

        $this->manager->tracer()?->instantSpan('sql', $name, $query->time, $metadata, $origin);
    }

    private function formatSql(QueryExecuted $query): string
    {
        try {
            return DB::getQueryGrammar()->substituteBindingsIntoRawSql($query->sql, $query->bindings);
        } catch (\Throwable) {
            return $query->sql;
        }
    }

    private function buildName(string $sql): string
    {
        $sql = trim($sql);
        $type = strtoupper(explode(' ', $sql)[0]);

        // SELECT ... FROM table / INSERT INTO table
        if (preg_match('/^(?:SELECT|INSERT)\s+.*?\s+(?:FROM|INTO)\s+[`"\[]?(\w+)/i', $sql, $matches)) {
            return "sql({$type} {$matches[1]})";
        }

        // UPDATE table SET ... / DELETE FROM table
        if (preg_match('/^(?:UPDATE|DELETE\s+FROM)\s+[`"\[]?(\w+)/i', $sql, $matches)) {
            return "sql({$type} {$matches[1]})";
        }

        // DELETE FROM table (alternative)
        if (preg_match('/^DELETE\s+.*?\s+FROM\s+[`"\[]?(\w+)/i', $sql, $matches)) {
            return "sql({$type} {$matches[1]})";
        }

        return "sql({$type})";
    }

    private function isExplainQuery(string $sql): bool
    {
        return str_starts_with(strtolower(ltrim($sql)), 'explain');
    }
}
