<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

class ViewCollector
{
    /** @var array<string, ProfileEntry[]> */
    private array $viewStack = [];

    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen('composing: *', function (string $eventName, array $payload) {
            $this->handleComposing($eventName, $payload);
        });

        Event::listen('creating: *', function (string $eventName, array $payload) {
            $this->handleCreating($eventName, $payload);
        });
    }

    private function handleComposing(string $eventName, array $payload): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('views')) {
            return;
        }

        $view = $payload[0] ?? null;
        if (! $view) {
            return;
        }

        $viewName = $view->getName();
        $viewPath = $view->getPath();

        $shortPath = $this->getShortPath($viewPath);
        $name = $shortPath ?: $viewName;

        $entry = new ProfileEntry(
            type: 'view',
            name: $name,
            startMs: $this->manager->getElapsedMs(),
            durationMs: null,
            parentId: $this->manager->getCurrentParentId(),
            metadata: [
                'view' => $viewName,
                'path' => $viewPath,
                'data_keys' => array_keys($view->getData()),
            ],
            origin: $this->manager->captureBacktrace()
        );

        $this->viewStack[$viewName][] = $entry;
        $this->manager->addEntry($entry);
        $this->manager->pushContext($entry->id);
    }

    private function handleCreating(string $eventName, array $payload): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        $view = $payload[0] ?? null;
        if (! $view) {
            return;
        }

        $viewName = $view->getName();

        if (! empty($this->viewStack[$viewName])) {
            $entry = array_pop($this->viewStack[$viewName]);
            $entry->stop($this->manager->getElapsedMs());
            $this->manager->popContext();

            if (empty($this->viewStack[$viewName])) {
                unset($this->viewStack[$viewName]);
            }
        }
    }

    private function getShortPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_contains($path, 'views/')) {
            $parts = explode('views/', $path);

            return end($parts);
        }

        return basename($path);
    }
}
