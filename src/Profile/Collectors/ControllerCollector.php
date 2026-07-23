<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\OpenTelemetry\ScopedSpan;
use LaraDumps\LaraDumps\Profile\ProfileManager;

class ControllerCollector
{
    private ?ScopedSpan $currentControllerSpan = null;

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
        /** @var array $action */
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

        $metadata = [
            'controller' => $controllerAction,
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'name' => $route->getName(),
        ];

        $this->currentControllerSpan = $this->manager->tracer()
            ?->beginScopedSpan('controller', $name, $metadata);
    }

    public function stopController(): void
    {
        if ($this->currentControllerSpan) {
            $this->currentControllerSpan->end();
            $this->currentControllerSpan = null;
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
