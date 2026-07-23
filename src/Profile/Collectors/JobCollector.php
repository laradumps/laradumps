<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\ProfileManager;
use OpenTelemetry\API\Trace\SpanInterface;

class JobCollector
{
    /** @var array<string, array{span: SpanInterface, metadata: array}> */
    private array $processingJobs = [];

    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen(JobQueued::class, fn ($event) => $this->handleQueued($event));
        Event::listen(JobProcessing::class, fn ($event) => $this->handleProcessing($event));
        Event::listen(JobProcessed::class, fn ($event) => $this->handleProcessed($event));
        Event::listen(JobFailed::class, fn ($event) => $this->handleFailed($event));
    }

    private function handleQueued(JobQueued $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('jobs')) {
            return;
        }

        $jobName = $this->getJobName($event);

        $metadata = [
            'job' => $jobName,
            'status' => 'queued',
            'connection' => $event->connectionName,
            'queue' => $event->queue ?? null,
        ];

        $origin = $this->manager->captureBacktrace();

        $this->manager->tracer()?->instantSpan('job', "job(queued: {$jobName})", 0, $metadata, $origin);
    }

    private function handleProcessing(JobProcessing $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('jobs')) {
            return;
        }

        $jobName = $this->getJobNameFromJob($event->job);
        $jobId = $event->job->getJobId();

        $metadata = [
            'job' => $jobName,
            'status' => 'processing',
            'job_id' => $jobId,
        ];

        $origin = $this->manager->captureBacktrace();

        $span = $this->manager->tracer()?->beginSpan('job', "job(processing: {$jobName})", $metadata, $origin);

        if ($span !== null) {
            $this->processingJobs[$jobId] = ['span' => $span, 'metadata' => $metadata];
        }
    }

    private function handleProcessed(JobProcessed $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $this->finishJob($event->job->getJobId(), ['status' => 'processed']);
    }

    private function handleFailed(JobFailed $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $this->finishJob($event->job->getJobId(), [
            'status' => 'failed',
            'exception' => $event->exception->getMessage(),
        ]);
    }

    private function finishJob(string $jobId, array $extraMetadata): void
    {
        if (! isset($this->processingJobs[$jobId])) {
            return;
        }

        $pending = $this->processingJobs[$jobId];
        unset($this->processingJobs[$jobId]);

        $this->manager->tracer()?->endSpan($pending['span'], array_merge($pending['metadata'], $extraMetadata));
    }

    private function getJobName(JobQueued $event): string
    {
        if (is_object($event->job)) {
            return class_basename(get_class($event->job));
        }

        return 'Unknown';
    }

    private function getJobNameFromJob(mixed $job): string
    {
        if (method_exists($job, 'displayName')) {
            return class_basename($job->displayName());
        }

        if (method_exists($job, 'resolveName')) {
            return class_basename($job->resolveName());
        }

        return 'Unknown';
    }
}
