<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Queue\Events\{JobFailed, JobProcessed, JobProcessing, JobQueued};
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\Contracts\TraceableContract;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, Payload};
use LaraDumps\LaraDumpsCore\Support\Dumper;

class JobsObserver implements TraceableContract
{
    use Traceable;

    private bool $enabled = false;

    private string $label = 'Job';

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

        if (!boolval(Config::get('send_jobs'))) {
            return $this->enabled;
        }

        return boolval(Config::get('send_jobs'));
    }

    private function generatePayload(object $event): Payload
    {
        [$pre, $id] = Dumper::dump(
            /* @phpstan-ignore-next-line */
            $event->job instanceof Job
                ? unserialize($event->job->payload()['data']['command'], ['allowed_classes' => true])
                : $event->job
        );

        $payload = new DumpPayload($pre);
        $payload->setDumpId($id);

        return $payload;
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send($payload);
        $dumps->label($this->label);
    }
}
