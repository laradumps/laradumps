<?php

namespace LaraDumps\LaraDumps\Observers;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\{JobProcessed, JobProcessing};
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Payloads\LogPayload;
use LaraDumps\LaraDumpsCore\Actions\{Config, Dumper};
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Support\CodeSnippet;

class LogObserver extends BaseObserver
{
    protected array $queries = [];

    protected Request $request;

    public function __construct()
    {
        $this->request = app(Request::class);
    }

    public function register(): void
    {
        if (! $this->isEnabled('logs')) {
            return;
        }

        Event::listen(MessageLogged::class, $this->onMessageLogged(...));
        Event::listen(QueryExecuted::class, $this->onQueryExecuted(...));
        Event::listen([JobProcessing::class, JobProcessed::class], fn () => $this->clearQueries());
    }

    private function onQueryExecuted(QueryExecuted $event): void
    {
        $this->queries[] = [
            'connectionName' => $event->connectionName,
            'time' => $event->time,
            'sql' => $event->sql,
            'bindings' => $event->bindings,
        ];
    }

    private function onMessageLogged(MessageLogged $event): void
    {
        $level = $event->level === 'debug' ? 'info' : $event->level;

        if (! $this->shouldLogMessage($event->message, $level) || $this->isEmailLog($event->message)) {
            return;
        }

        $payload = new LogPayload([
            'message' => $event->message,
            'level' => $level,
            'context' => Dumper::dump($this->resolveContext($event->context)),
            'queries' => $this->formattedQueries(),
            'request' => [
                'headers' => $this->requestHeaders(),
                'body' => $this->requestBody(),
                'routeContext' => $this->applicationRouteContext(),
            ],
        ]);

        if (! empty($event->context['exception']) && $event->context['exception'] instanceof \Throwable) {
            $payload->setCodeSnippet((new CodeSnippet())->fromException($event->context['exception']));
        }

        (new LaraDumps())->send($payload);
    }

    private function shouldLogMessage(string $message, string $level): bool
    {
        $config = (array) Config::get('logs', []);

        if (! isset($config[$level]) || $config[$level] !== true) {
            return false;
        }

        return match ($level) {
            'vendor' => str_contains($message, 'vendor'),
            'deprecated_message' => str_contains($message, 'deprecated'),
            default => true,
        };
    }

    private function isEmailLog(string $message): bool
    {
        return Str::containsAll($message, ['From:', 'To:', 'Subject:']);
    }

    private function resolveContext(?array $context): array
    {
        if (! blank($context)) {
            return $context;
        }

        if (class_exists(\Illuminate\Support\Facades\Context::class)) {
            return \Illuminate\Support\Facades\Context::all();
        }

        return [];
    }

    private function requestHeaders(): array
    {
        return array_map(fn (array $header) => implode(', ', $header), $this->request->headers->all());
    }

    private function requestBody(): ?string
    {
        $payload = $this->request->all();

        if (empty($payload)) {
            return null;
        }

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return str_replace('\\', '', (string) $json);
    }

    private function applicationRouteContext(): array
    {
        $route = $this->request->route();

        if (! $route) {
            return [];
        }

        return array_filter([
            'controller' => $route->getActionName(),
            'routeName' => $route->getName(),
            'middleware' => implode(', ', array_map(fn ($m) => $m instanceof Closure ? 'Closure' : $m, $route->gatherMiddleware())),
        ]);
    }

    private function formattedQueries(): array
    {
        return array_map(fn (array $query) => [
            'connectionName' => $query['connectionName'],
            'time' => $query['time'],
            'sql' => $this->interpolateBindings($query['sql'], $query['bindings']),
        ], $this->queries);
    }

    private function interpolateBindings(string $sql, array $bindings): ?string
    {
        foreach ($bindings as $binding) {
            $replacement = match (gettype($binding)) {
                'integer', 'double' => $binding,
                'NULL' => 'NULL',
                default => "'$binding'",
            };

            $sql = preg_replace('/\?/', (string) $replacement, $sql, 1);
        }

        return $sql;
    }

    private function clearQueries(): void
    {
        $this->queries = [];
    }
}
