<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Payloads\JobPayload;
use LaraDumps\LaraDumpsCore\Actions\{Config, Dumper};
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{Payload};

class JobsObserver
{
    private bool $enabled = false;

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

            $payload = $this->generatePayload($event, get_class($event));

            $this->sendPayload($payload);
        });
    }

    public function getLabelClassNameBased(string $className): string
    {
        return match ($className) {
            JobQueued::class     => 'Queued',
            JobProcessing::class => 'Processing',
            JobProcessed::class  => 'Processed',
            JobFailed::class     => 'Failed',
            default              => 'Stale'
        };
    }

    public function isEnabled(): bool
    {
        if (!boolval(Config::get('observers.jobs', false))) {
            return $this->enabled;
        }

        return boolval(Config::get('observers.jobs', false));
    }

    public function generatePayload(object $event, string $className): Payload
    {
        $dump = Dumper::dump(
            /* @phpstan-ignore-next-line */
            $event->job instanceof Job && $event?->job->payload()
                ? unserialize($event->job->payload()['data']['command'], ['allowed_classes' => true]) // @phpstan-ignore-line
                : $event->job
        );

        $jobId       = method_exists($event, 'payload') ? $event->payload()['uuid'] : $event->job->payload()['uuid'];
        $displayName = method_exists($event, 'payload') ? $event->payload()['displayName'] : $event->job->payload()['displayName'];

        if (method_exists($event->job, 'payload')) {
            $jobId = $event->job->payload()['uuid'];
        }

        return new JobPayload(
            job: $dump,
            status: $this->getLabelClassNameBased($className),
            jobId: $jobId,
            displayName: $displayName
        );
    }

    protected function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps();

        $dumps->send($payload);
    }
}
