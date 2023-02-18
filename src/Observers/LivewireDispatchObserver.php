<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Support\Str;
use LaraDumps\LaraDumps\Actions\{Config, MakeFileHandler};
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LivewireEventsPayload;
use LaraDumps\LaraDumps\Support\{Dumper, IdeHandle};
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

                    $component         = get_class($component);

                    /** @phpstan-ignore-next-line */
                    $reflector         = new ReflectionClass($component);
                    $componentBasePath = strval($reflector->getFileName());

                    $data = [
                        'event'            => $notificationId,
                        'dispatch'         => true,
                        'component'        => $component,
                        'componentHandler' => [
                            'handler' => MakeFileHandler::handle(['file' => $componentBasePath, 'line' => 1]),
                            'path'    => Str::of(strval($component))->replace(config('livewire.class_namespace') . '\\', ''),
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
        return (bool) Config::get('send_livewire_dispatch');
    }
}
