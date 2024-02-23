<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\{CallbackEvent, Event, Schedule};
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\Payloads\{Payload, TableV2Payload};
use Spatie\Backtrace\Backtrace;

class ScheduledCommandObserver
{
    use Traceable;

    private bool $enabled = false;

    private string $label = 'Schedule';

    public function register(): void
    {
        $this->enabled = $this->isEnabled();

        \Illuminate\Support\Facades\Event::listen(CommandStarting::class, function (CommandStarting $event) {
            if (!$this->isEnabled() || (
                $event->command !== 'schedule:run' &&
                    $event->command !== 'schedule:finish'
            )
            ) {
                return;
            }

            $backtrace = Backtrace::create();
            $backtrace = $backtrace->applicationPath(base_path());
            $frame     = $this->parseFrame($backtrace);

            collect(app(Schedule::class)->events())
                ->each(function ($event) use ($frame) {
                    $event->then(function () use ($event, $frame) {
                        $payload = $this->generatePayload($event);
                        $payload->setFrame($frame);
                        $this->sendPayload($payload);
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
        if (!boolval(Config::get('send_scheduled_command'))) {
            return $this->enabled;
        }

        return boolval(Config::get('send_scheduled_command'));
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps();

        $dumps->send($payload);
        $dumps->label($this->label);
    }

    private function generatePayload(Event $event): Payload
    {
        return new TableV2Payload([
            'Command'     => $event instanceof CallbackEvent ? 'Closure' : $event->command,
            'Description' => $event->description,
            'Expression'  => $event->expression,
            'Timezone'    => $event->timezone,
            'User'        => $event->user,
            'Output'      => $this->getEventOutput($event),
        ]);
    }

    protected function getEventOutput(Event $event): string|null
    {
        if (!$event->output ||
            $event->output === $event->getDefaultOutput() ||
            $event->shouldAppendOutput ||
            !file_exists($event->output)) {
            return '';
        }

        return trim(file_get_contents($event->output)); /** @phpstan-ignore-line */
    }
}
