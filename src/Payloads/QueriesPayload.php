<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class QueriesPayload extends Payload
{
    public function __construct(
        private array $queries = [],
        private string $screen = 'queries',
        private string $label = ''
    ) {}

    public function type(): string
    {
        return 'queries';
    }

    public function content(): array
    {
        return $this->queries;
    }

    public function toScreen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function withLabel(): array|Label
    {
        return new Label($this->label);
    }
}
