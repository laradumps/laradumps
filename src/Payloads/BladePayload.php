<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\Payload;

class BladePayload extends Payload
{
    public function __construct(
        public mixed $dump,
    ) {
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump' => $this->dump,
        ];
    }
}
