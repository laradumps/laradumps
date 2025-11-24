<?php

namespace LaraDumps\LaraDumps\Brain;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class BrainPayload extends Payload
{
    public function __construct(
        public ?array $task = null,
        public ?array $process = null,
        public mixed $payloadData = null,
        public ?string $runProcessId = null,
        public array $meta = [],
        public string $status = '',
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
            'task' => $this->task,
            'process' => $this->process,
            'payload_data' => $this->payloadData,
            'run_process_id' => $this->runProcessId,
            'meta' => $this->meta,
            'status' => $this->status,
        ];
    }
}
