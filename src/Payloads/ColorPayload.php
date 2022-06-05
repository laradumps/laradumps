<?php

namespace LaraDumps\LaraDumps\Payloads;

class ColorPayload extends Payload
{
    public function __construct(
        public string $color
    ) {
    }

    public function type(): string
    {
        return 'color';
    }

    /** @return array<string> */
    public function content(): array
    {
        return [
            'color' => $this->color,
        ];
    }
}
