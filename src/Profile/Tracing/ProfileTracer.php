<?php

declare(strict_types=1);

namespace LaraDumps\LaraDumps\Profile\Tracing;

use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

final class ProfileTracer
{
    /** @var string[] Entry ids of the currently open scoped spans. */
    private array $spanStack = [];

    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public static function isAvailable(): bool
    {
        return true;
    }

    public function instantSpan(
        string $type,
        string $name,
        float $durationMs,
        array $metadata = [],
        ?array $origin = null
    ): void {
        $this->manager->addEntry(new ProfileEntry(
            type: $type,
            name: $name,
            startMs: max(0, $this->manager->getElapsedMs() - $durationMs),
            durationMs: $durationMs,
            parentId: $this->currentParentId(),
            metadata: $metadata,
            origin: $origin,
        ));
    }

    public function beginSpan(
        string $type,
        string $name,
        array $metadata = [],
        ?array $origin = null
    ): ProfileSpan {
        $entry = new ProfileEntry(
            type: $type,
            name: $name,
            startMs: $this->manager->getElapsedMs(),
            parentId: $this->currentParentId(),
            metadata: $metadata,
            origin: $origin,
        );

        $this->manager->addEntry($entry);

        return new ProfileSpan($entry);
    }

    public function endSpan(ProfileSpan $span, ?array $metadata = null): void
    {
        $span->finish($this->manager->getElapsedMs(), $metadata);
    }

    public function beginScopedSpan(
        string $type,
        string $name,
        array $metadata = [],
        ?array $origin = null
    ): ScopedSpan {
        $entry = new ProfileEntry(
            type: $type,
            name: $name,
            startMs: $this->manager->getElapsedMs(),
            parentId: $this->currentParentId(),
            metadata: $metadata,
            origin: $origin,
        );

        $this->manager->addEntry($entry);
        $this->spanStack[] = $entry->id;

        return new ScopedSpan($entry, $this);
    }

    public function endScopedSpan(ProfileEntry $entry): void
    {
        $entry->stop($this->manager->getElapsedMs());

        if ($this->spanStack !== []) {
            array_pop($this->spanStack);
        }
    }

    public function shutdown(): void {}

    private function currentParentId(): ?string
    {
        if ($this->spanStack === []) {
            return null;
        }

        return end($this->spanStack);
    }
}
