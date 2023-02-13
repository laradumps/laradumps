<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\{CallbackEvent, Event, Schedule};
use LaraDumps\LaraDumps\Concerns\Traceable;
use LaraDumps\LaraDumps\Contracts\TraceableContract;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{DumpPayload, Payload};
use LaraDumps\LaraDumps\Support\Dumper;

class ScheduledCommandObserver implements TraceableContract
{
    use Traceable;

    private bool $enabled = false;

    private string $label = 'Schedule';

    private array $trace = [];

    public function register(): void
    {
        $this->enabled = $this->isEnabled();

        \Illuminate\Support\Facades\Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if (!$this->isEnabled() || (
                $event->command     !== 'schedule:run' &&
                    $event->command !== 'schedule:finish'
            )
            ) {
                return;
            }

            collect(app(Schedule::class)->events())->each(function ($event) {
                $event->then(function () use ($event) {
                    $this->sendPayload(
                        $this->generatePayload($event)
                    );
                });
            });
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

        if (config('laradumps.send_scheduled_command')) {
            return $this->enabled;
        }

        return true;
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send($payload);
        $dumps->label($this->label);
    }

    private function generatePayload(Event $event): Payload
    {
        return new DumpPayload(Dumper::dump([
            'command'     => $event instanceof CallbackEvent ? 'Closure' : $event->command,
            'description' => $event->description,
            'expression'  => $event->expression,
            'timezone'    => $event->timezone,
            'user'        => $event->user,
            'output'      => $this->getEventOutput($event),
        ]));
    }

    protected function getEventOutput(Event $event): string|null
    {
        if (!$event->output                               ||
            $event->output === $event->getDefaultOutput() ||
            $event->shouldAppendOutput                    ||
            !file_exists($event->output)) {
            return '';
        }

        return trim(file_get_contents($event->output)); /** @phpstan-ignore-line */
    }
}
