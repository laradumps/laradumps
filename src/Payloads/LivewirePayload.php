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
        $component = Str::of(base_path() . '/' . $this->component['component'] . '.php')
            ->replace('\\', '/', )
            ->replace('App', 'app');

        $path = Str::of($this->component['component'])
            ->replace(config('livewire.class_namespace') . '\\', '');

        return [
            'handler' => IdeHandle::makeFileHandler($component, '1'),
            'path'    => $path,
            'line'    => 1,
        ];
    }

    public function type(): string
    {
        return 'livewire';
    }
}
