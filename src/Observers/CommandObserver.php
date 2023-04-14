<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Actions\Config;
use LaraDumps\LaraDumpsCore\Concerns\Traceable;
use LaraDumps\LaraDumpsCore\Contracts\TraceableContract;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, Payload};
use LaraDumps\LaraDumpsCore\Support\Dumper;

class CommandObserver implements TraceableContract
{
    use Traceable;

    private bool $enabled = false;

    private string $label = 'Command';

    public function register(): void
    {
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

        if (!(bool) boolval(Config::get('send_commands'))) {
            return $this->enabled;
        }

        return boolval(Config::get('send_commands'));
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
