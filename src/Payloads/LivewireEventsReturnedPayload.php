<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Support\IdeHandle;

class LivewireEventsReturnedPayload extends Payload
{
    public function __construct(
        protected array $event
    ) {
    }

    public function content(): array
    {
        return [
            'event' => $this->event,
        ];
    }

    public function customHandle(): array
    {
        $component = Str::of(base_path() . '/' . $this->event['component'] . '.php')->replace('\\', '/', )->replace('App', 'app');

        return [
            'handler' => IdeHandle::makeFileHandler($component, '1'),
            'path'    => $this->event['component'],
            'line'    => 1,
        ];
    }

    public function type(): string
    {
        return 'livewire-events-returned';
    }
}
