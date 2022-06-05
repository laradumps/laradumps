<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Support\IdeHandle;

class LivewirePayload extends Payload
{
    public function __construct(
        protected array $component
    ) {
    }

    public function content(): array
    {
        return [
            'component' => $this->component,
        ];
    }

    public function customHandle(): array
    {
        $component = Str::of(base_path() . '/' . $this->component['component'] . '.php')->replace('\\', '/', )->replace('App', 'app');

        return [
            'handler' => IdeHandle::makeFileHandler($component, '1'),
            'path'    => $this->component['component'],
            'line'    => 1,
        ];
    }

    public function type(): string
    {
        return 'livewire';
    }
}
