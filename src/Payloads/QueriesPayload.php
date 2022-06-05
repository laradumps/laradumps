<?php

namespace LaraDumps\LaraDumps\Payloads;

class QueriesPayload extends Payload
{
    public function __construct(
        private array $queries = [],
        public string $file = '',
        public string $line = '',
        public array  $trace = [],
    ) {
    }

    public function type(): string
    {
        return 'queries';
    }

    public function content(): array
    {
        return [
            'queries' => $this->queries,
            'file'    => $this->file,
            'line'    => $this->line,
        ];
    }
}
