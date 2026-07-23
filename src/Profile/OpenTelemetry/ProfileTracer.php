<?php

declare(strict_types=1);

namespace LaraDumps\LaraDumps\Profile\OpenTelemetry;

use LaraDumps\LaraDumps\Profile\ProfileManager;
use OpenTelemetry\API\Trace\{SpanBuilderInterface, SpanInterface, TracerInterface};
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

final readonly class ProfileTracer
{
    private TracerProvider $provider;

    private TracerInterface $tracer;

    public static function isAvailable(): bool
    {
        return class_exists('OpenTelemetry\SDK\Trace\TracerProvider')
            && class_exists('OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor')
            && interface_exists('OpenTelemetry\API\Trace\SpanInterface');
    }

    public function __construct(ProfileManager $manager)
    {
        $this->provider = new TracerProvider(
            new SimpleSpanProcessor(new LaraDumpsSpanExporter($manager))
        );

        $this->tracer = $this->provider->getTracer('laradumps.profile');
    }

    public function instantSpan(
        string $type,
        string $name,
        float $durationMs,
        array $metadata = [],
        ?array $origin = null
    ): void {
        $endNanos = (int) (microtime(true) * 1_000_000_000);
        $startNanos = $endNanos - (int) ($durationMs * 1_000_000);

        $span = $this->builder($name, $type, $metadata, $origin)
            ->setStartTimestamp($startNanos)
            ->startSpan();

        $span->end($endNanos);
    }

    public function beginScopedSpan(
        string $type,
        string $name,
        array $metadata = [],
        ?array $origin = null
    ): ScopedSpan {
        $span = $this->builder($name, $type, $metadata, $origin)
            ->setStartTimestamp((int) (microtime(true) * 1_000_000_000))
            ->startSpan();

        return new ScopedSpan($span, $span->activate());
    }

    public function beginSpan(
        string $type,
        string $name,
        array $metadata = [],
        ?array $origin = null
    ): SpanInterface {
        return $this->builder($name, $type, $metadata, $origin)
            ->setStartTimestamp((int) (microtime(true) * 1_000_000_000))
            ->startSpan();
    }

    public function endSpan(SpanInterface $span, ?array $metadata = null): void
    {
        if ($metadata !== null) {
            $span->setAttribute('ld.metadata', json_encode($metadata));
        }

        $span->end((int) (microtime(true) * 1_000_000_000));
    }

    public function shutdown(): void
    {
        $this->provider->forceFlush();
        $this->provider->shutdown();
    }

    private function builder(string $name, string $type, array $metadata, ?array $origin): SpanBuilderInterface
    {
        return $this->tracer->spanBuilder($name)
            ->setAttribute('ld.type', $type)
            ->setAttribute('ld.metadata', json_encode($metadata))
            ->setAttribute('ld.origin', $origin !== null ? json_encode($origin) : null);
    }
}
