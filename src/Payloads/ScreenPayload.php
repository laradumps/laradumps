<?php

namespace LaraDumps\LaraDumps\Payloads;

class ScreenPayload extends Payload
{
    public function __construct(
        public string $screenName,
        public bool $classAttr = false,
        public int $raiseIn = 0,
    ) {
    }

    public function type(): string
    {
        return 'screen';
    }

    /** @return array<string|mixed> */
    public function content(): array
    {
        return [
            'screenName' => $this->screenName,
            'raiseIn'    => $this->raiseIn,
        ];
    }
}
