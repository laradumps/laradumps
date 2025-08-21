<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Event};
use LaraDumps\LaraDumps\Actions\{IgnoreQuerySqlPattern, IgnoreRoutePattern};
use LaraDumps\LaraDumps\Payloads\QueriesPayload;
use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;
use Spatie\Backtrace\{Backtrace, Frame};
use Throwable;

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

            if ($this->isExplainQuery($query->sql)) {
                return;
            }

            if (IgnoreRoutePattern::execute()) {
                return;
            }

            if (IgnoreQuerySqlPattern::execute($sql)) {
                return;
            }

            $queries = $this->buildQueryPayload($query, $sql);

            $payload = new QueriesPayload($queries);
            $payload->setFrame($this->parseFrame());

            $this->sendPayload($payload);
        } catch (Throwable) {
        }
    }

    private function isExplainQuery(string $sql): bool
    {
        return str_starts_with(strtolower(ltrim($sql)), 'explain format=json');
    }

    private function parseFrame(): array|Frame
    {
        return app(LaraDumps::class)->parseFrame(Backtrace::create());
    }

    private function sendPayload(QueriesPayload $payload): void
    {
        $dumper = new LaraDumps();
        $dumper->send($payload, withFrame: false);

        $this->label && $dumper->label($this->label);
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
            'explain_nodes' => $this->maybeExplainQuery($query->sql),
        ];
    }

    private function maybeExplainQuery(string $sql): array
    {
        if (! Config::get('queries.explain', false)) {
            return [];
        }

        if (! str_starts_with(strtolower($sql), 'select')) {
            return [];
        }

        return $this->extractExplain($sql);
    }

    public function extractExplain(string $originalQuery): array
    {
        try {
            $result = DB::select('EXPLAIN FORMAT=JSON '.$originalQuery);
            DB::connection()->enableQueryLog();

            $jsonText = $result[0]->{'EXPLAIN'} ?? null;

            if (! $jsonText) {
                return [];
            }

            /** @var array $data */
            $data = json_decode($jsonText, true);

            $problematicNodes = [];
            $this->analyzeJsonExplainNode($data['query_block'] ?? $data, $problematicNodes);

            return $problematicNodes;
        } catch (Throwable) {
            return [];
        }
    }

    private function analyzeJsonExplainNode(array $node, array &$problematicNodes): void
    {
        $this->checkTableAccess($node, $problematicNodes);

        foreach (['nested_loop', 'query_block', 'attached_condition', 'grouping_operation', 'table'] as $childKey) {
            $this->processChildNode($node, $childKey, $problematicNodes);
        }
    }

    private function checkTableAccess(array $node, array &$problematicNodes): void
    {
        if (! isset($node['table'])) {
            return;
        }

        $accessType = strtolower($node['table']['access_type'] ?? '');
        $usedKey = ! empty($node['table']['key']);

        if ($accessType === 'all' && ! $usedKey) {
            $problematicNodes[] = $node['table'];
        }
    }

    private function processChildNode(array $node, string $childKey, array &$problematicNodes): void
    {
        if (empty($node[$childKey]) || ! is_array($node[$childKey])) {
            return;
        }

        if ($this->isSequentialArray($node[$childKey])) {
            foreach ($node[$childKey] as $childNode) {
                if (is_array($childNode)) {
                    $this->analyzeJsonExplainNode($childNode, $problematicNodes);
                }
            }

            return;
        }

        $this->analyzeJsonExplainNode($node[$childKey], $problematicNodes);
    }

    private function isSequentialArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    private function resolveRequestContext(): array
    {
        $request = request();
        $queryString = $request->getQueryString();

        return [
            'origin' => $this->resolveRequestOrigin($request),
            'argv' => $request->server('argv'),
            'uri' => $this->buildRequestUri($request, $queryString),
            'method' => $request->getMethod(),
        ];
    }

    private function resolveRequestOrigin(Request $request): string
    {
        return ($request->server('argv') && $request->server('SCRIPT_NAME') === 'artisan')
            ? 'console'
            : 'http';
    }

    private function buildRequestUri(Request $request, ?string $queryString): string
    {
        return str($request->getPathInfo().($queryString ? "?$queryString" : ''))
            ->ltrim('/')
            ->toString();
    }
}
