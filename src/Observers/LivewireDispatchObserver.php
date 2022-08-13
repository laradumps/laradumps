<?php

namespace LaraDumps\LaraDumps\Observers;

use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{LivewireEventsPayload, LivewireEventsReturnedPayload};
use LaraDumps\LaraDumps\Support\{Dumper, IdeHandle};
use Livewire\{Component, Response};
use ReflectionClass;

class LivewireDispatchObserver
{
    public function register(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::listen('component.dehydrate', function ($component) {
                if (!$this->isEnabled()) {
                    return;
                }

                $dispatch = collect((array) $component->getDispatchQueue());

                if ($dispatch->isEmpty()) {
                    return;
                }

                $dispatch->each(function ($event) use ($component) {
                    $notificationId = strval(data_get($event, 'event'));
                    $params         = (array) data_get($event, 'data');

                    $reflector         = new ReflectionClass((object) get_class($component));
                    $componentBasePath = strval($reflector->getFileName());

                    $data = [
                        'event'            => $notificationId,
                        'dispatch'         => true,
                        'component'        => get_class($component),
                        'componentHandler' => [
                            'handler' => IdeHandle::makeFileHandler($componentBasePath, '1'),
                            'path'    => get_class($component),
                            'line'    => 1,
                        ],
                        'params' => Dumper::dump($params),
                    ];

                    $dumps = new LaraDumps(notificationId: $notificationId);

                    $dumps->send(new LivewireEventsPayload($data));

                    $dumps->toScreen('Dispatch');
                });
            });
        }
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_livewire_dispatch');
    }
}
