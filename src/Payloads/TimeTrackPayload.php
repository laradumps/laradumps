<?php

namespace LaraDumps\LaraDumps\Payloads;

class TimeTrackPayload extends Payload
{
    /**
     * Clock script executiontime
     *
     * @param string $reference Reference name used in each call
     */
    public function __construct(
        public string $reference
    ) {
    }

    public function type(): string
    {
        return 'time-track';
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        return [
            'timeTrackerId' => base64_encode(config('app.name') . strtolower($this->reference)),
            'label'         => $this->reference,
            'time'          => microtime(true),
        ];
    }
}
