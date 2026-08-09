<?php

declare(strict_types=1);

namespace LaraDumps\LaraDumps\Profile\Tracing;

use LaraDumps\LaraDumps\Profile\ProfileEntry;

final readonly class ScopedSpan
{
    public function __construct(
        private ProfileEntry $entry,
        private ProfileTracer $tracer,
    ) {}

    public function end(): void
    {
        $this->tracer->endScopedSpan($this->entry);
    }

    public function spanId(): string
    {
        return $this->entry->id;
    }
}
