<?php

namespace LaraDumps\LaraDumps\Payloads;

class DumpPayload extends Payload
{
    public function __construct(
        public mixed $dump,
        public mixed $originalContent = null,
    ) {
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump'            => $this->dump,
            'originalContent' => $this->originalContent,
        ];
    }
}
