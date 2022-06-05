<?php

namespace LaraDumps\LaraDumps\Payloads;

class LabelPayload extends Payload
{
    /**
     * ColorPayload constructor.
     * @param string $label
     */
    public function __construct(
        public string $label
    ) {
    }

    public function type(): string
    {
        return 'label';
    }

    public function content(): array
    {
        return [
            'label' => $this->label,
        ];
    }
}
