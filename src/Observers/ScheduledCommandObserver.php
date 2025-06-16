<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\{CallbackEvent, Event, Schedule};
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{Payload, TableV2Payload};

class ScheduledCommandObserver extends BaseObserver
{
    protected string $label = 'Schedule';

    public function register(): void
    {
        \Illuminate\Support\Facades\Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if (! $this->isEnabled('scheduled_commands')) {
                return;
            }

            if (
                $event->command !== 'schedule:run' &&
                $event->command !== 'schedule:finish'
            ) {
                return;
            }

            collect(app(Schedule::class)->events())
                ->each(function ($event) {
                    $event->then(function () use ($event) {
                        $payload = $this->generatePayload($event);
                        $this->sendPayload($payload);
                    });
                });
        });
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps();

        $dumps->send($payload);
    }

    private function generatePayload(Event $event): Payload
    {
        return new TableV2Payload([
            'Command' => $event instanceof CallbackEvent ? 'Closure' : $event->command,
            'Description' => $event->description,
            'Expression' => $event->expression,
            'Timezone' => $event->timezone,
            'User' => $event->user,
            'Output' => $this->getEventOutput($event),
        ], screen: 'scheduled commands', label: $this->label);
    }

    protected function getEventOutput(Event $event): ?string
    {
        if (! $event->output ||
            $event->output === $event->getDefaultOutput() ||
            $event->shouldAppendOutput ||
            ! file_exists($event->output)) {
            return '';
        }

        return trim((string) file_get_contents($event->output));
    }
}
