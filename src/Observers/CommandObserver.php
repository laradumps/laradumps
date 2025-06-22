<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\{DumpPayload, Payload};

class CommandObserver extends BaseObserver
{
    protected string $label = 'Command';

    public function register(): void
    {
        Event::listen(CommandFinished::class, fn (object $event) => $this->handle($event));
    }

    public function handle(object $event): void
    {
        if (! $this->isEnabled('commands')) {
            return;
        }

        $payload = $this->generatePayload($event);

        $this->sendPayload($payload);
    }

    private function generatePayload(object $event): Payload
    {
        return new DumpPayload(Dumper::dump([
            /* @phpstan-ignore-next-line */
            'command' => $event->command ?? $event->input->getArguments()['command'] ?? 'default',
            'exit_code' => $event->exitCode, /** @phpstan-ignore-line */
            'arguments' => $event->input->getArguments(), /** @phpstan-ignore-line */
            'options' => $event->input->getOptions(), /** @phpstan-ignore-line */
        ]));
    }

    private function sendPayload(Payload $payload): void
    {
        $dumps = new LaraDumps();

        $dumps->send($payload);
        $dumps->label($this->label);
    }
}
