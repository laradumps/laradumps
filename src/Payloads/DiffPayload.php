<?php

namespace LaraDumps\LaraDumps\Payloads;

class DiffPayload extends Payload
{
    public function __construct(
        public mixed $argument,
        public bool $splitDiff,
    ) {
    }

    public function type(): string
    {
        return 'diff';
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        return [
            'argument'  => $this->argument,
            'splitDiff' => $this->splitDiff,
        ];
    }
}
