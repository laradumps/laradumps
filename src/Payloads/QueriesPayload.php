<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class QueriesPayload extends Payload
{
    public function __construct(
        private array $queries = [],
        private string $screen = 'Queries',
        private string $label = ''
    ) {
    }

    public function type(): string
    {
        return 'queries';
    }

    public function content(): array
    {
        return $this->queries;
    }

    public function screen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function label(): array|Label
    {
        return new Label($this->label);
    }
}
