<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

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

        $entry = new ProfileEntry(
            type: 'app',
            name: "bootstrap({$shortName})",
            startMs: $this->manager->getElapsedMs(),
            durationMs: null,
            parentId: $this->manager->getCurrentParentId(),
            metadata: [
                'bootstrapper' => $bootstrapperClass,
            ],
            origin: null
        );

        $this->manager->addEntry($entry);
    }
}
