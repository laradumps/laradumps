<?php

namespace LaraDumps\LaraDumps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use LaraDumps\LaraDumps\Observers\{CacheObserver, CommandObserver, HttpClientObserver, JobsObserver, QueryObserver};
use LaraDumps\LaraDumps\Payloads\{MailablePayload, MarkdownPayload, ModelPayload, RoutesPayload};
use LaraDumps\LaraDumpsCore\LaraDumps as BaseLaraDumps;

class LaraDumps extends BaseLaraDumps
{
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
    public function httpOn(string $label = null): self
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
    public function cacheOn(string $label = null): self
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
}
