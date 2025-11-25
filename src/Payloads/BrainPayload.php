<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class BrainPayload extends Payload
{
    public function __construct(
        public mixed $className = '',
        public string $runProcessId = '',
        public mixed $payload = null,
        public array $meta = [],
        public string $status = '',
        public string $type = 'process'
    ) {}

    public function type(): string
    {
        return 'brain';
    }

    public function toScreen(): array|Screen
    {
        return new Screen('brain');
    }

    public function withLabel(): array|Label
    {
        return [];
    }

    public function content(): array
    {
        return [
            'className' => $this->className,
            'run_process_id' => $this->runProcessId,
            'payload' => $this->payload,
            'meta' => $this->meta,
            'status' => $this->status,
            'type' => $this->type,
        ];
    }
}
