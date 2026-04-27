<?php

namespace LaraDumps\LaraDumps\Profile;

class ProfileEntry
{
    public string $id;

    public string $type;

    public string $name;

    public float $startMs;

    public ?float $durationMs = null;

    public ?string $parentId = null;

    public array $metadata = [];

    public ?string $originClass = null;

    public ?string $originMethod = null;

    public ?string $originFile = null;

    public ?int $originLine = null;

    public function __construct(
        string $type,
        string $name,
        float $startMs,
        ?float $durationMs = null,
        ?string $parentId = null,
        array $metadata = [],
        ?array $origin = null
    ) {
        $this->id = uniqid('pe_', true);
        $this->type = $type;
        $this->name = $name;
        $this->startMs = $startMs;
        $this->durationMs = $durationMs;
        $this->parentId = $parentId;
        $this->metadata = $metadata;

        if ($origin) {
            $this->originClass = $origin['class'] ?? null;
            $this->originMethod = $origin['method'] ?? null;
            $this->originFile = $origin['file'] ?? null;
            $this->originLine = $origin['line'] ?? null;
        }
    }

    public function stop(float $endMs): void
    {
        $this->durationMs = $endMs - $this->startMs;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'start_ms' => $this->startMs,
            'duration_ms' => $this->durationMs !== null ? round($this->durationMs, 3) : null,
            'parent_id' => $this->parentId,
            'metadata' => $this->metadata,
            'origin' => [
                'class' => $this->originClass,
                'method' => $this->originMethod,
                'file' => $this->originFile,
                'line' => $this->originLine,
            ],
        ];
    }
}
