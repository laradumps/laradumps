<?php

namespace LaraDumps\LaraDumps\Payloads;

class ValidJsonPayload extends Payload
{
    public function type(): string
    {
        return 'json-validate';
    }
}
