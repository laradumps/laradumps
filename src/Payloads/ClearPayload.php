<?php

namespace LaraDumps\LaraDumps\Payloads;

class ClearPayload extends Payload
{
    public function type(): string
    {
        return 'clear';
    }

    /** @return array<string> */
    public function content(): array
    {
        return [];
    }
}
