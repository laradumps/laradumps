<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\Payload;

class TimeTrackPayload extends Payload
{
    /**
     * Clock script execution time
     *
     * @param bool $stop
     */
    public function __construct(
        public bool $stop = false
    ) {
    }

    public function type(): string
    {
        return 'time-track';
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        $content = [
            'timeTrackerId' => uniqid(),
            'time'          => microtime(true),
        ];

        if ($this->stop) {
            $content['end_time'] = microtime(true);
        }

        return $content;
    }
}
