<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Payloads\BrainPayload;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use ReflectionClass;
use Spatie\Backtrace\{Backtrace, Frame};

class BrainObserver extends BaseObserver
{
    public function register(): void
    {
        Event::listen([
            'Brain\\Workflows\\Events\\*',
            'Brain\\Actions\\Events\\*',
        ], fn (string $eventName, array $data) => $this->handle($data[0]));
    }

    private function handle(object $event): void
    {
        if (! $this->isEnabled('brain')) {
            return;
        }

        $runWorkflowId = $event->runWorkflowId ?? null;

        if (blank($runWorkflowId)) {
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

                if (str_contains($class, 'Brain\\Workflow')) {
                    return false;
                }

                if (str_contains($class, 'Brain\\Action')) {
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

        $payload = $this->generatePayload($event, (string) $runWorkflowId);

        $payload->setFrame(filled($frame) ? [
            'file' => $frame->file,
            'line' => $frame->lineNumber,
        ] : [
            'file' => 'unknown',
            'line' => 0,
        ]);

        $this->sendPayload($payload);
    }

    private function generatePayload(object $event, string $runWorkflowId): Payload
    {
        $className = get_class($event);

        $payload = $event->payload;
        $meta = $event->meta;

        $type = 'action';

        $brainClassName = match (true) {
            str_contains($className, 'Actions') => $event->action,
            str_contains($className, 'Workflows') => $event->workflow,
            default => 'Unknown',
        };

        return new BrainPayload(
            className: $brainClassName,
            runWorkflowId: $runWorkflowId,
            payload: Dumper::dump($payload),
            meta: $meta,
            status: (new ReflectionClass($event))->getShortName(),
            type: $type
        );
    }

    private function sendPayload(Payload $payload): void
    {
        (new LaraDumps())->send($payload, withFrame: false);
    }
}
