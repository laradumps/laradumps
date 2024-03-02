<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumpsCore\Actions\Config;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, Payload};
use LaraDumps\LaraDumpsCore\Support\Dumper;

class JobsObserver
{
    private bool $enabled = false;

    private ?string $label = null;

    public function register(): void
    {
        Event::listen([
            JobQueued::class,
            JobProcessing::class,
            JobProcessed::class,
            JobFailed::class,
        ], function (object $event) {
            if (!$this->isEnabled()) {
                return;
            }

            $payload = $this->generatePayload($event);

            $this->sendPayload($payload, get_class($event));
        });
    }

    public function getLabelClassNameBased(string $className): string
    {
        return match ($className) {
            JobQueued::class     => 'Job - Queued',
            JobProcessing::class => 'Job - Processing',
            JobProcessed::class  => 'Job - Processed',
            JobFailed::class     => 'Job - Failed',
            default              => 'Job'
        };
    }

    public function enable(string $label = null): void
    {
        if ($label) {
            $this->label = $label;
        }

        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        if (!boolval(Config::get('observers.jobs'))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.jobs'));
    }

    public function generatePayload(object $event): Payload
    {
        [$pre, $id] = Dumper::dump(
            /* @phpstan-ignore-next-line */
            $event->job instanceof Job && $event?->job->payload()
                ? unserialize($event->job->payload()['data']['command'], ['allowed_classes' => true])
                : $event->job
        );

        $payload = new DumpPayload($pre);
        $payload->setDumpId($id);

        return $payload;
    }

    protected function sendPayload(Payload $payload, string $className): void
    {
        $dumps = new LaraDumps();

        $dumps->send($payload);
        $dumps->label($this->label ?? $this->getLabelClassNameBased($className));
    }
}
