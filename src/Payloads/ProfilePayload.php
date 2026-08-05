<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class ProfilePayload extends Payload
{
    public function __construct(
        private readonly string $profileId,
        private readonly string $label,
        private readonly float $startTime,
        private readonly ?float $endTime,
        private readonly float $totalDurationMs,
        private readonly array $entries,
        private readonly array $summary
    ) {
        $this->setOriginalContent([
            'label' => $label,
            'total_duration_ms' => $totalDurationMs,
        ]);
    }

    public static function fromProfileData(array $data): self
    {
        return new self(
            profileId: $data['profile_id'],
            label: $data['label'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            totalDurationMs: $data['total_duration_ms'],
            entries: $data['entries'],
            summary: $data['summary']
        );
    }

    public function type(): string
    {
        return 'profiler';
    }

    public function content(): array
    {
        return [
            'profile_id' => $this->profileId,
            'label' => $this->label,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'total_duration_ms' => $this->totalDurationMs,
            'entries' => $this->entries,
            'summary' => $this->summary,
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('profiler');
    }

    public function withLabel(): array|Label
    {
        return new Label($this->label);
    }
}
