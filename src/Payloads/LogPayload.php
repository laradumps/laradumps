<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\Payload;

class LogPayload extends Payload
{
    public function __construct(
        protected array $value
    ) {
    }

    public function type(): string
    {
        return 'log_application';
    }

    public function content(): array
    {
        return $this->value;
    }
}
