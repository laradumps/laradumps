<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{LivewireEventsPayload, LivewireEventsReturnedPayload};
use LaraDumps\LaraDumps\Support\{Dumper, IdeHandle};
use ReflectionClass;

class LivewireEventsObserver
{
    public function register(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::listen('action.returned', function ($component, $eventName, $returned) {
                if (!$this->isEnabled()) {
                    return;
                }

                if (in_array($eventName, $component->getEventsBeingListenedFor())) {
                    $reflector         = new ReflectionClass((object) get_class($component));
                    $componentBasePath = strval($reflector->getFileName());

                    $component = get_class($component);

                    $data = [
                        'component'        => $component,
                        'componentHandler' => [
                            'handler' => IdeHandle::makeFileHandler($componentBasePath, '1'),
                            'path'    => Str::of(strval($component))->replace(config('livewire.class_namespace') . '\\', ''),
                            'line'    => 1,
                        ],
                        'event'    => $eventName,
                        'returned' => Dumper::dump($returned),
                    ];

                    $dumps = new LaraDumps(notificationId: 'ds-event-' . $eventName);

                    $dumps->send(new LivewireEventsReturnedPayload($data));
                }
            });

            \Livewire\Livewire::listen('component.dehydrate', function ($component, $request) {
                if (!$this->isEnabled()) {
                    return;
                }

                $updates = collect((array) data_get($request, 'request.updates'));

                $updates->filter(function ($update) {
                    return strval(data_get($update, 'type')) === 'fireEvent';
                })->each(function ($event) use ($request) {
                    $eventName = strval(data_get($event, 'payload.event'));
                    if ($eventName === '$refresh') {
                        $componentName     = strval(data_get($request, 'request.fingerprint.name'));

                        $data = [
                            'component' => $componentName,
                            'event'     => $eventName,
                            'returned'  => [],
                        ];

                        $dumps = new LaraDumps(notificationId: 'ds-event-' . $eventName);

                        $dumps->send(new LivewireEventsReturnedPayload($data));
                    }
                });

                $events = collect((array) $component->getEventQueue());

                if ($events->isEmpty()) {
                    return;
                }

                $events->each(function ($event) use ($updates, $request, $component) {
                    $update = $updates->filter(function ($update) {
                        return strval(data_get($update, 'type')) === 'callMethod';
                    })->transform(function ($update) use ($request) {
                        return [
                            'method'    => strval(data_get($update, 'payload.method')),
                            'component' => strval(data_get($request, 'request.fingerprint.name')),
                        ];
                    })->first();

                    $notificationId = strval(data_get($event, 'event'));
                    $params         = (array) data_get($event, 'params');

                    $component         = get_class($component);

                    /** @phpstan-ignore-next-line */
                    $reflector         = new ReflectionClass($component);
                    $componentBasePath = strval($reflector->getFileName());

                    $data = [
                        'event'            => $notificationId,
                        'dispatch'         => false,
                        'method'           => strval(data_get($update, 'method')),
                        'componentHandler' => [
                            'handler' => IdeHandle::makeFileHandler($componentBasePath, '1'),
                            'path'    => Str::of(strval($component))->replace(config('livewire.class_namespace') . '\\', ''),
                            'line'    => 1,
                        ],
                        'params'   => Dumper::dump($params),
                        'returned' => [],
                    ];

                    $dumps = new LaraDumps(notificationId: 'ds-event-' . $notificationId);

                    $dumps->send(new LivewireEventsPayload($data));

                    $dumps->toScreen('Events');
                });
            });
        }
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_livewire_events');
    }
}
