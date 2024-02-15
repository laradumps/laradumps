<?php

namespace LaraDumps\LaraDumps\Observers\Contracts;

use LaraDumps\LaraDumpsCore\Payloads\Payload;

interface GeneratePayload
{
    public function generatePayload(object $event): Payload;
}
