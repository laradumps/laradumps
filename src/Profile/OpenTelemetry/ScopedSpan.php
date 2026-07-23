<?php

declare(strict_types=1);

namespace LaraDumps\LaraDumps\Profile\OpenTelemetry;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\ScopeInterface;

final readonly class ScopedSpan
{
    public function __construct(
        private SpanInterface $span,
        private ScopeInterface $scope,
    ) {}

    public function end(): void
    {
        $this->scope->detach();
        $this->span->end((int) (microtime(true) * 1_000_000_000));
    }
}
