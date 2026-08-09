<?php

declare(strict_types=1);

namespace LaraDumps\LaraDumps\Profile\Tracing;

use LaraDumps\LaraDumps\Profile\ProfileEntry;

final readonly class ProfileSpan
{
    public function __construct(
        private ProfileEntry $entry,
    ) {}

    public function finish(float $endMs, ?array $metadata = null): void
    {
        if ($metadata !== null) {
            $this->entry->metadata = $metadata;
        }

        $this->entry->stop($endMs);
    }
}
