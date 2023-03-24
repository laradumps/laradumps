<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\Payload;

class EventPayload extends Payload
{
    /** @var object|mixed|null */
    protected mixed $event = null;

    public function __construct(protected string $eventName, protected array $payload)
    {
        if (class_exists($eventName)) {
            $this->event = $payload[0];
        }
    }

    public function content(): array
    {
        return [
            'name'              => $this->eventName,
            'event'             => $this->event ?: null,
            'payload'           => count($this->payload) ? $this->payload : null,
            'class_based_event' => !is_null($this->event),
        ];
    }

    public function type(): string
    {
        return 'events';
    }
}
