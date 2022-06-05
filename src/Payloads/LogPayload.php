<?php

namespace LaraDumps\LaraDumps\Payloads;

class LogPayload extends Payload
{
    public function __construct(
        protected array $value
    ) {
    }

    public function type(): string
    {
        return 'log';
    }

    public function content(): array
    {
        return [
            'value' => $this->value,
        ];
    }
}
