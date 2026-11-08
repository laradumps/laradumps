<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class LivewirePayload extends Payload
{
    public function __construct(public array $livewire) {}

    public function type(): string
    {
        return 'livewire';
    }

    public function toScreen(): array|Screen
    {
        return new Screen('livewire');
    }

    public function withLabel(): array|Label
    {
        return [];
    }

    public function content(): array
    {
        return $this->livewire;
    }
}
