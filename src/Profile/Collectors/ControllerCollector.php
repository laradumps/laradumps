<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\{ProfileEntry, ProfileManager};

class ControllerCollector
{
    private ?ProfileEntry $currentControllerEntry = null;

    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        Event::listen(RouteMatched::class, function (RouteMatched $event) {
            $this->handleRouteMatched($event);
        });
    }

    private function handleRouteMatched(RouteMatched $event): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('controller')) {
            return;
        }

        $route = $event->route;
        $action = $route->getAction();

        $controllerAction = $action['controller'] ?? null;

        if (! $controllerAction) {
            if (isset($action['uses']) && $action['uses'] instanceof \Closure) {
                $name = 'Closure';
            } else {
                return;
            }
        } else {
            $name = $this->formatControllerAction($controllerAction);
        }

        $entry = new ProfileEntry(
            type: 'controller',
            name: $name,
            startMs: $this->manager->getElapsedMs(),
            durationMs: null,
            parentId: $this->manager->getCurrentParentId(),
            metadata: [
                'controller' => $controllerAction,
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
            ],
            origin: null
        );

        $this->currentControllerEntry = $entry;
        $this->manager->addEntry($entry);
        $this->manager->pushContext($entry->id);
    }

    public function stopController(): void
    {
        if ($this->currentControllerEntry) {
            $this->currentControllerEntry->stop($this->manager->getElapsedMs());
            $this->manager->popContext();
            $this->currentControllerEntry = null;
        }
    }

    private function formatControllerAction(string $controllerAction): string
    {
        if (str_contains($controllerAction, '@')) {
            [$controller, $method] = explode('@', $controllerAction);
            $shortController = class_basename($controller);

            return "{$shortController}::{$method}()";
        }

        return class_basename($controllerAction);
    }
}
