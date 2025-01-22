<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class QueriesPayload extends Payload
{
    public function __construct(
        private array $queries = [],
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
        return new Screen('Queries');
    }

    public function label(): array|Label
    {
        return [];
    }
}
