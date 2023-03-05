<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\Concerns\Traceable;
use LaraDumps\LaraDumps\Contracts\TraceableContract;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{DumpPayload, Payload};
use LaraDumps\LaraDumps\Support\Dumper;

class JobsObserver implements TraceableContract
{
    use Traceable;

    private bool $enabled = false;

    private string $label = 'Job';

    private array $trace = [];

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

            $this->sendPayload(
                $this->generatePayload($event)
            );
        });
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
        $this->trace = array_slice($this->findSource(), 0, 5)[0] ?? [];

        if (!(bool) boolval(Config::get('send_jobs'))) {
            return $this->enabled;
        }

        return true;
    }

    private function generatePayload(object $event): Payload
    {
        return new DumpPayload(Dumper::dump(
            /* @phpstan-ignore-next-line */
            $event->job instanceof Job
                ? unserialize($event->job->payload()['data']['command'], ['allowed_classes' => true])
                : $event->job
        ));
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send($payload);
        $dumps->label($this->label);
    }
}
