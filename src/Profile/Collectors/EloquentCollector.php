<?php

namespace LaraDumps\LaraDumps\Profile\Collectors;

use Illuminate\Support\Facades\Event;
use LaraDumps\LaraDumps\Profile\ProfileManager;

class EloquentCollector
{
    public function __construct(
        private readonly ProfileManager $manager
    ) {}

    public function register(): void
    {
        $events = [
            'eloquent.retrieved',
            'eloquent.creating',
            'eloquent.created',
            'eloquent.updating',
            'eloquent.updated',
            'eloquent.saving',
            'eloquent.saved',
            'eloquent.deleting',
            'eloquent.deleted',
            'eloquent.restoring',
            'eloquent.restored',
        ];

        foreach ($events as $event) {
            Event::listen("{$event}: *", function (string $eventName, array $payload) use ($event) {
                $this->handle($event, $eventName, $payload);
            });
        }
    }

    private function handle(string $baseEvent, string $fullEventName, array $payload): void
    {
        if (! $this->manager->isActive()) {
            return;
        }

        if (! $this->manager->shouldCapture('eloquent')) {
            return;
        }

        $model = $payload[0] ?? null;
        if (! $model) {
            return;
        }

        $modelClass = get_class($model);
        $shortName = class_basename($modelClass);
        $action = str_replace('eloquent.', '', $baseEvent);

        $name = "eloquent({$shortName})";

        $metadata = [
            'model' => $modelClass,
            'action' => $action,
            'key' => $model->getKey(),
        ];

        $origin = $this->manager->captureBacktrace();

        $this->manager->tracer()?->instantSpan('eloquent', $name, 0, $metadata, $origin);
    }
}
