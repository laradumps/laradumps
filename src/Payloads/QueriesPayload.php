<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\Payload;

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
}
