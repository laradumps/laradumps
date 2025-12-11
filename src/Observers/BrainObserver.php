<?php

namespace LaraDumps\LaraDumps\Observers;

use Brain\Processes\Events\{Error as ProcessError, Processed as ProcessProcessed, Processing as ProcessProcessing};
use Brain\Tasks\Events\{Cancelled as TaskCancelled, Error as TaskError, Processed as TaskProcessed, Processing as TaskProcessing, Skipped as TaskSkipped};
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Payloads\BrainPayload;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Spatie\Backtrace\{Backtrace, Frame};

class BrainObserver extends BaseObserver
{
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
        if (! class_exists(ProcessProcessing::class) && ! class_exists(TaskProcessing::class)) {
            return;
        }

        if (! $this->isEnabled('brain')) {
            return;
        }

        if (blank($event->runProcessId)) {
            return;
        }

        $backtrace = Backtrace::create();

        /** @var Frame $frame */
        $frame = collect($backtrace->frames())
            ->filter(fn ($frame) => $frame->applicationFrame)
            ->filter(function ($frame) {
                if (! $frame->applicationFrame) {
                    return false;
                }

                $class = $frame->class ?? '';
                $file = $frame->file ?? '';

                if (str_contains($class, 'Brain\\Process')) {
                    return false;
                }

                if (str_contains($class, 'Brain\\Task')) {
                    return false;
                }

                if (str_contains($class, 'BrainObserver')) {
                    return false;
                }

                if (str_contains($file, 'vendor')) {
                    return false;
                }

                return true;
            })
            ->first();

        $payload = $this->generatePayload($event);

        $payload->setFrame(filled($frame) ? [
            'file' => $frame->file,
            'line' => $frame->lineNumber,
        ] : [
            'file' => 'unknown',
            'line' => 0,
        ]);

        $this->sendPayload($payload);
    }

    private function generatePayload(object $event): Payload
    {
        $className = get_class($event);

        $runProcessId = $event->runProcessId;
        $payload = $event->payload;
        $meta = $event->meta;

        if (str_contains($className, 'Brain\\Processes\\Events')) {
            $process = $event->process;

            return new BrainPayload(
                className: $process,
                runProcessId: (string) $runProcessId,
                payload: Dumper::dump($payload),
                meta: $meta,
                status: $this->getLabelClassNameBased($className),
                type: 'process'
            );
        }

        $task = $event->task;

        return new BrainPayload(
            className: $task,
            runProcessId: $runProcessId,
            payload: Dumper::dump($payload),
            meta: $meta,
            status: $this->getLabelClassNameBased($className),
            type: 'task'
        );
    }

    private function getLabelClassNameBased(string $className): string
    {
        return match (true) {
            $className === ProcessProcessing::class, $className === TaskProcessing::class => 'Processing',
            $className === ProcessProcessed::class, $className === TaskProcessed::class => 'Processed',
            $className === ProcessError::class, $className === TaskError::class => 'Error',
            $className === TaskCancelled::class => 'Cancelled',
            $className === TaskSkipped::class => 'Skipped',
            default => 'Stale',
        };
    }

    private function sendPayload(Payload $payload): void
    {
        (new LaraDumps())->send($payload, withFrame: false);
    }
}
