<?php

namespace LaraDumps\LaraDumps\Payloads;

class DiffPayload extends Payload
{
    public function __construct(
        public mixed $first,
        public mixed $second,
        public bool $col,
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
            'first'  => $this->first,
            'second' => $this->second,
            'col'    => $this->col,
        ];
    }
}
