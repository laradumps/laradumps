<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Concerns\Traceable;
use LaraDumps\LaraDumps\Contracts\TraceableContract;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{DumpPayload, Payload};
use LaraDumps\LaraDumps\Support\Dumper;

class CommandObserver implements TraceableContract
{
    use Traceable;

    private bool $enabled = false;

    private string $label = 'Command';

    private array $trace = [];

    public function register(): void
    {
        $this->enabled = $this->isEnabled();

        Event::listen(CommandFinished::class, function (object $event) {
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

        if ((bool) boolval(config('laradumps.send_commands'))) {
            return $this->enabled;
        }

        return true;
    }

    private function generatePayload(object $event): Payload
    {
        return new DumpPayload(Dumper::dump([
            /* @phpstan-ignore-next-line */
            'command'   => $event->command ?? $event->input->getArguments()['command'] ?? 'default',
            'exit_code' => $event->exitCode, /** @phpstan-ignore-line */
            'arguments' => $event->input->getArguments(), /** @phpstan-ignore-line */
            'options'   => $event->input->getOptions(), /** @phpstan-ignore-line */
        ]));
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send($payload);
        $dumps->label($this->label);
    }
}
