<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use LaraDumps\LaraDumps\Observers\{CacheObserver,
    CommandObserver,
    GateObserver,
    HttpClientObserver,
    JobsObserver,
    QueryObserver,
    ScheduledCommandObserver};
use LaraDumps\LaraDumps\Payloads\{MailablePayload, MarkdownPayload, ModelPayload, RoutesPayload};
use LaraDumps\LaraDumpsCore\LaraDumps as BaseLaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\DumpPayload;
use LaraDumps\LaraDumpsCore\Support\Dumper;

class LaraDumps extends BaseLaraDumps
{
    protected function beforeWrite(mixed $args): \Closure
    {
        return function () use ($args) {
            if ($args instanceof Model) {
                $payload = new ModelPayload($args);

                return [
                    $payload,
                    uniqid(),
                ];
            }

            if ($args instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                if (!$args->items()[0] instanceof Model) {
                    return;
                }

                $models    = [];
                $paginator = clone $args;

                /** @var Model $item */
                foreach ($paginator->items() as $item) {
                    $models[] = [
                        'className'  => get_class($item),
                        'attributes' => $item->attributesToArray(),
                        'relations'  => $item->relationsToArray(),
                    ];
                }

                $paginator->setCollection(collect($models));

                [$pre, $id] = Dumper::dump($paginator);

                $payload = new DumpPayload($pre);
                $payload->setDumpId($id);
            }

            return parent::beforeWrite($args)();
        };
    }

    /**
     * Send Routes
     *
     */
    public function routes(mixed ...$except): self
    {
        $this->send(new RoutesPayload($except));

        return $this;
    }

    /**
     * Shows model attributes and relationship
     *
     */
    public function model(Model ...$models): LaraDumps
    {
        foreach ($models as $model) {
            if ($model instanceof Model) {
                $payload = new ModelPayload($model);
                $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
                $this->send($payload);
            }
        }

        return $this;
    }

    /**
     * Display all queries that are executed with custom label
     *
     */
    public function queriesOn(string $label = null): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(QueryObserver::class)->setTrace($trace);
        app(QueryObserver::class)->enable($label);
    }

    /**
     * Stop displaying queries
     *
     */
    public function queriesOff(): void
    {
        app(QueryObserver::class)->disable();
    }

    /**
     * Send rendered mailable
     *
     */
    public function mailable(Mailable $mailable): self
    {
        $payload = new MailablePayload($mailable);
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);

        $this->send($payload);

        return $this;
    }

    /**
     * Display all HTTP Client requests that are executed with custom label
     */
    public function httpOn(string $label = ''): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(HttpClientObserver::class)->setTrace($trace);
        app(HttpClientObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop displaying HTTP Client requests
     */
    public function httpOff(): void
    {
        app(HttpClientObserver::class)->disable();
    }

    /*
     * Sends rendered markdown
     */
    public function markdown(string $markdown): self
    {
        $payload = new MarkdownPayload($markdown);
        $payload->setTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
        $this->send($payload);

        return $this;
    }

    /**
     * Dump all Jobs that are dispatched with custom label
     */
    public function jobsOn(string $label = null): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(JobsObserver::class)->setTrace($trace);
        app(JobsObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop dumping Jobs
     */
    public function jobsOff(): void
    {
        app(JobsObserver::class)->disable();
    }

    /**
     * Dump all Jobs that are dispatched with custom label
     */
    public function cacheOn(string $label = ''): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(CacheObserver::class)->setTrace($trace);
        app(CacheObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop dumping Jobs
     */
    public function cacheOff(): void
    {
        app(CacheObserver::class)->disable();
    }

    /**
     * Dump all Commands with custom label
     */
    public function commandsOn(string $label = null): self
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(CommandObserver::class)->setTrace($trace);
        app(CommandObserver::class)->enable($label);

        return $this;
    }

    /**
     * Stop dumping Commands
     */
    public function commandsOff(): void
    {
        app(CommandObserver::class)->disable();
    }

    /**
     * Dump Scheduled Commands with custom label
     */
    public function scheduledCommandOn(string $label = null): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(ScheduledCommandObserver::class)->setTrace($trace);
        app(ScheduledCommandObserver::class)->enable($label);
    }

    /**
     * Dump all Gate & Policy checkes with custom label
     */
    public function gateOn(string $label = null): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];

        app(GateObserver::class)->setTrace($trace);
        app(GateObserver::class)->enable($label);
    }

    /**
     * Stop dumping Scheduled Commands
     */
    public function scheduledCommandOff(): void
    {
        app(ScheduledCommandObserver::class)->disable();
    }

    /**
     * Stop dumping Gate
     */
    public function gateOff(): void
    {
        app(GateObserver::class)->disable();
    }
}
