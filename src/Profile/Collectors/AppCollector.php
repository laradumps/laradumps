<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Foundation\Events\Bootstrapped;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

class AppCollector
{
    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen(Bootstrapped::class, function (Bootstrapped $event) {
            $this->handleBootstrapped($event);
        });
    }

    private function handleBootstrapped(Bootstrapped $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('app')) {
            return;
        }

        $bootstrapperClass = get_class($event->bootstrapper);
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
