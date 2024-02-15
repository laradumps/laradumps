<?php

namespace LaraDumps\LaraDumps\Observers\Concerns;

use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

trait SendPayload
{
    protected function sendPayload(Payload $payload, string $className): void
    {
        $dumps = new LaraDumps(trace: $this->trace);

        $dumps->send($payload);
        $dumps->label($this->label ?? $this->getLabelClassNameBased($className));
    }
}
