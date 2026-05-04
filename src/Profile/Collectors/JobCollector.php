<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

class JobCollector
{
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

        $entry = new ProfileEntry(
            type: 'job',
            name: "job(queued: {$jobName})",
            startMs: $this->manager->getElapsedMs(),
            durationMs: null,
            parentId: $this->manager->getCurrentParentId(),
            metadata: [
                'job' => $jobName,
                'status' => 'queued',
                'connection' => $event->connectionName,
                'queue' => $event->queue ?? null,
            ],
            origin: $this->manager->captureBacktrace()
        );

        $this->manager->addEntry($entry);
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

        $entry = new ProfileEntry(
            type: 'job',
            name: "job(processing: {$jobName})",
            startMs: $this->manager->getElapsedMs(),
            durationMs: null,
            parentId: $this->manager->getCurrentParentId(),
            metadata: [
                'job' => $jobName,
                'status' => 'processing',
                'job_id' => $jobId,
            ],
            origin: $this->manager->captureBacktrace()
        );

        $this->processingJobs[$jobId] = $entry;
        $this->manager->addEntry($entry);
    }

    private function handleProcessed(JobProcessed $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $jobId = $event->job->getJobId();

        if (isset($this->processingJobs[$jobId])) {
            $entry = $this->processingJobs[$jobId];
            $entry->stop($this->manager->getElapsedMs());
            $entry->metadata['status'] = 'processed';
            unset($this->processingJobs[$jobId]);
        }
    }

    private function handleFailed(JobFailed $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $jobId = $event->job->getJobId();

        if (isset($this->processingJobs[$jobId])) {
            $entry = $this->processingJobs[$jobId];
            $entry->stop($this->manager->getElapsedMs());
            $entry->metadata['status'] = 'failed';
            $entry->metadata['exception'] = $event->exception->getMessage();
            unset($this->processingJobs[$jobId]);
        }
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
