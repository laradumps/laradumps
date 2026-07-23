<?php

declare(strict_types=1);

namespace LaraDumps\LaraDumps\Profile\OpenTelemetry;

use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\{SpanDataInterface, SpanExporterInterface};

final class LaraDumpsSpanExporter implements SpanExporterInterface
{
    use SpanExporterTrait;

    private const INVALID_SPAN_ID = '0000000000000000';

    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    protected function doExport(iterable $spans): bool
    {
        foreach ($spans as $span) {
            $this->manager->addEntry($this->toEntry($span));
        }

        return true;
    }

    private function toEntry(SpanDataInterface $span): ProfileEntry
    {
        $attributes = $span->getAttributes()->toArray();

        $type = (string) ($attributes['ld.type'] ?? 'app');
        $metadata = $this->decode($attributes['ld.metadata'] ?? null);
        $origin = $this->decode($attributes['ld.origin'] ?? null) ?: null;

        $startMs = ($span->getStartEpochNanos() / 1_000_000) - $this->manager->getStartTime();
        $durationMs = ($span->getEndEpochNanos() - $span->getStartEpochNanos()) / 1_000_000;

        $entry = new ProfileEntry(
            type: $type,
            name: $span->getName(),
            startMs: round(max($startMs, 0), 3),
            durationMs: round($durationMs, 3),
            parentId: $this->resolveParentId($span),
            metadata: $metadata,
            origin: $origin,
        );

        $entry->id = $span->getSpanId();

        return $entry;
    }

    private function resolveParentId(SpanDataInterface $span): ?string
    {
        $parentSpanId = $span->getParentSpanId();

        if ($parentSpanId === '' || $parentSpanId === self::INVALID_SPAN_ID) {
            return $this->manager->getRootEntryId();
        }

        return $parentSpanId;
    }

    private function decode(mixed $value): array
    {
        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
