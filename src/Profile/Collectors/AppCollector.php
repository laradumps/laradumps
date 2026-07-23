<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\ProfileManager;

class AppCollector
{
    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen('bootstrapped: *', function (string $eventName, array $payload) {
            $bootstrapperClass = substr($eventName, strlen('bootstrapped: '));
            $this->handleBootstrapped($bootstrapperClass);
        });
    }

    private function handleBootstrapped(string $bootstrapperClass): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('app')) {
            return;
        }

        $shortName = class_basename($bootstrapperClass);

        $metadata = ['bootstrapper' => $bootstrapperClass];

        $this->manager->tracer()?->instantSpan('app', "bootstrap({$shortName})", 0, $metadata);
    }
}
