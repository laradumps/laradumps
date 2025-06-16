<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class JobPayload extends Payload
{
    public function __construct(
        public array $job,
        public string $status,
        public string $jobId,
        public string $displayName
    ) {}

    public function type(): string
    {
        return 'jobs';
    }

    public function content(): array
    {
        return [
            'job' => $this->job,
            'status' => $this->status,
            'job_id' => $this->jobId,
            'display_name' => $this->displayName,
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('jobs');
    }

    public function withLabel(): array|Label
    {
        return [];
    }
}
