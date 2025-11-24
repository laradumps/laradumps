<?php

namespace LaraDumps\LaraDumps\Brain;

use Brain\Processes\Events\{Error as ProcessError, Processed as ProcessProcessed, Processing as ProcessProcessing};
use Brain\Tasks\Events\{Cancelled as TaskCancelled,
    Error as TaskError,
    Processed as TaskProcessed,
    Processing as TaskProcessing,
    Skipped as TaskSkipped};
use Composer\InstalledVersions;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Observers\BaseObserver;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

class BrainObserver extends BaseObserver
{
    private static ?string $brainVersion = null;

    public function register(): void
    {
        Event::listen([
            ProcessProcessing::class,
            ProcessProcessed::class,
            ProcessError::class,
            TaskProcessing::class,
            TaskProcessed::class,
            TaskCancelled::class,
            TaskSkipped::class,
            TaskError::class,
        ], fn (object $event) => $this->handle($event));
    }

    private function handle(object $event): void
    {
        if (! is_null(self::$brainVersion)) {
            return;
        }

        self::$brainVersion = InstalledVersions::getversion('r2luna/brain');

        if (is_null(self::$brainVersion)) {
            return;
        }

        if (! $this->isEnabled('brain')) {
            return;
        }

        $this->sendPayload(
            $this->generatePayload($event)
        );
    }

    private function generatePayload(object $event): Payload
    {
        $className = get_class($event);

        $isProcessEvent = str_contains($className, 'Brain\\Processes\\Events');

        $status = $this->getLabelClassNameBased($className);

        return new BrainPayload(
            task: $isProcessEvent ? null : Dumper::dump($event->task),
            process: $isProcessEvent ? Dumper::dump($event->process) : ($event->process ? Dumper::dump($event->process) : null),
            payloadData: Dumper::dump($event->payload),
            runProcessId: $event->runProcessId ? (string) $event->runProcessId : null,
            meta: $event->meta,
            status: $status,
        );
    }

    private function getLabelClassNameBased(string $className): string
    {
        return match (true) {
            $className === ProcessProcessing::class,
            $className === TaskProcessing::class => 'Processing',

            $className === ProcessProcessed::class,
            $className === TaskProcessed::class => 'Processed',

            $className === ProcessError::class,
            $className === TaskError::class => 'Error',

            $className === TaskCancelled::class => 'Cancelled',

            $className === TaskSkipped::class => 'Skipped',

            default => 'Stale',
        };
    }

    private function sendPayload(Payload $payload): void
    {
        (new LaraDumps())->send($payload);
    }
}
