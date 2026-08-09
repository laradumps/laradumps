<?php

namespace LaraDumps\LaraDumps;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use LaraDumps\LaraDumps\Livewire\Support\Debug;
use LaraDumps\LaraDumps\Observers\{CacheObserver, CommandObserver, GateObserver, HttpClientObserver, ProfileObserver, QueryObserver, ScheduledCommandObserver};
use LaraDumps\LaraDumps\Payloads\{ContextPayload, MailablePayload, MarkdownPayload, ModelPayload, ProfilePayload, RoutesPayload};
use LaraDumps\LaraDumps\Profile\ProfileManager;
use LaraDumps\LaraDumps\Support\JobContext;
use LaraDumps\LaraDumpsCore\Dispatcher\Dispatcher;
use LaraDumps\LaraDumpsCore\LaraDumps as BaseLaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Livewire\Volt\Component;

class LaraDumps extends BaseLaraDumps
{
    protected function beforeWrite(mixed $args): Closure
    {
        return function () use ($args) {
            if ($args instanceof Model) {
                $payload = new ModelPayload($args);

                return [
                    $payload,
                    uniqid(),
                ];
            }

            if (class_exists(Component::class)
                && $args instanceof Component) {
                (new Debug())->debug($args->getId());

                return [[], null];
            }

            return parent::beforeWrite($args)();
        };
    }

    /**
     * Send Routes
     */
    public function routes(mixed ...$except): self
    {
        $this->send(new RoutesPayload($except));

        return $this;
    }

    /**
     * Shows model attributes and relationship
     */
    public function model(Model ...$models): LaraDumps
    {
        foreach ($models as $model) {
            if ($model instanceof Model) {
                $payload = new ModelPayload($model);
                $this->send($payload);
            }
        }

        return $this;
    }

    /**
     * Display all queries that are executed with custom label
     */
    public function queriesOn(string $label = ''): void
    {
        app(QueryObserver::class)->enable($label);
    }

    /**
     * Stop displaying queries
     */
    public function queriesOff(): void
    {
        app(QueryObserver::class)->disable();
    }

    /**
     * Send rendered mailable
     */
    public function mailable(Mailable $mailable): self
    {
        $payload = new MailablePayload($mailable);

        $this->send($payload);

        return $this;
    }

    /**
     * Display all HTTP Client requests that are executed with custom label
     */
    public function httpOn(string $label = ''): self
    {
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
        $this->send($payload);

        return $this;
    }

    /**
     * Dump all Jobs that are dispatched with custom label
     */
    public function cacheOn(string $label = ''): self
    {
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
    public function commandsOn(?string $label = null): self
    {
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
    public function scheduledCommandOn(?string $label = null): void
    {
        app(ScheduledCommandObserver::class)->enable($label);
    }

    /**
     * Dump all Gate & Policy checkes with custom label
     */
    public function gateOn(?string $label = null): void
    {
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

    /**
     * Sends context information to the dump.
     */
    public function withContext(string|array ...$keys): static
    {
        $payload = new ContextPayload($keys);
        $this->send($payload);

        return $this;
    }

    public function startProfile(?string $label = null): self
    {
        app(ProfileObserver::class)->start($label);

        return $this;
    }

    public function stopProfile(): self
    {
        $profileData = app(ProfileObserver::class)->stop();

        if (! empty($profileData)) {
            $payload = ProfilePayload::fromProfileData($profileData);
            $this->send($payload);
        }

        return $this;
    }

    public function captureProfileForTermination(): self
    {
        app(ProfileObserver::class)->finalize();

        return $this;
    }

    public function flushProfile(): self
    {
        $profileData = app(ProfileObserver::class)->buildData();

        if (! empty($profileData)) {
            $this->send(ProfilePayload::fromProfileData($profileData));
        }

        return $this;
    }

    public function profile(Closure $callback, ?string $label = null): mixed
    {
        $this->startProfile($label);

        try {
            $result = $callback();
        } finally {
            $this->stopProfile();
        }

        return $result;
    }

    public function measure(string $name, callable $callback, string $type = 'app', array $metadata = []): mixed
    {
        return app(ProfileManager::class)->measure($name, $callback, $type, $metadata);
    }

    protected function dispatchPayload(Payload $payload): void
    {
        $data = $payload->toArray();

        $data['related_job'] = $payload->type() === 'jobs' ? null : JobContext::current();

        (new Dispatcher())->handle($data);
    }
}
