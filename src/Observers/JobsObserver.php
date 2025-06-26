<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Payloads\JobPayload;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use LaraDumps\LaraDumpsCore\Support\CodeSnippet;

class JobsObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen([
            JobQueued::class,
            JobProcessing::class,
            JobProcessed::class,
            JobFailed::class,
        ], fn (object $event) => $this->handle($event));
    }

    private function handle(object $event): void
    {
        if (! $this->isEnabled('jobs')) {
            return;
        }

        $payload = $this->generatePayload($event);

        if ($event instanceof JobFailed) {
            $exception = $event->exception;
            $snippet = (new CodeSnippet())->fromDebugBacktrace($exception->getTrace());
            $payload->setCodeSnippet($snippet);
        }

        $this->sendPayload($payload);
    }

    private function generatePayload(object $event): Payload
    {
        $className = get_class($event);
        $job = $this->extractJob($event);

        $jobId = $this->extractJobPayloadAttribute($event, 'uuid');
        $displayName = $this->extractJobPayloadAttribute($event, 'displayName');

        return new JobPayload(
            job: Dumper::dump($job),
            status: $this->getLabelClassNameBased($className),
            jobId: $jobId,
            displayName: $displayName
        );
    }

    private function extractJob(object $event): mixed
    {
        if (! isset($event->job)) {
            return $event;
        }

        /** @phpstan-ignore-next-line  */
        if ($event->job instanceof Job && method_exists($event->job, 'payload')) {
            $payload = $event->job->payload();

            return isset($payload['data']['command'])
                ? unserialize($payload['data']['command'], ['allowed_classes' => true])
                : $event->job;
        }

        return $event->job;
    }

    private function extractJobPayloadAttribute(object $event, string $attribute): string
    {
        if (method_exists($event, 'payload') && isset($event->payload()[$attribute])) {
            return $event->payload()[$attribute];
        }

        if (isset($event->job) && method_exists($event->job, 'payload') && isset($event->job->payload()[$attribute])) {
            return $event->job->payload()[$attribute];
        }

        return 'unknown';
    }

    private function getLabelClassNameBased(string $className): string
    {
        return match ($className) {
            JobQueued::class => 'Queued',
            JobProcessing::class => 'Processing',
            JobProcessed::class => 'Processed',
            JobFailed::class => 'Failed',
            default => 'Stale',
        };
    }

    private function sendPayload(Payload $payload): void
    {
        (new LaraDumps())->send($payload);
    }
}
