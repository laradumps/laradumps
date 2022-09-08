<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LivewirePayload;
use LaraDumps\LaraDumps\Support\{Dumper, IdeHandle};
use ReflectionClass;

class LivewireComponentsObserver
{
    public function register(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::listen('view:render', function (View $view) {
                if (!$this->isEnabled()) {
                    return;
                }

                $component = $view->getData()['_instance'];

                if (filled(config('laradumps.livewire_components'))) {
                    $livewireComponents = Str::of(strval(config('laradumps.livewire_components')))->explode(',');

                    if (!Str::contains(strval(get_class($component)), $livewireComponents)) {
                        return;
                    }
                }

                if (in_array(get_class($component), (array) (config('laradumps.ignore_livewire_components')))) {
                    return;
                }

                $properties = $component->getPublicPropertiesDefinedBySubClass();

                if (boolval(config('laradumps.send_livewire_protected_properties'))) {
                    $properties + $component->getProtectedOrPrivatePropertiesDefinedBySubClass();
                }

                $properties['id'] = $component->id;

                $data = [
                    'data' => Dumper::dump($properties),
                ];

                $viewPath = $this->getViewPath($view);

                $data['name']        = $component->getName();
                $data['view']        = Str::of($view->name())->replace('livewire.', '');
                $data['viewHandler'] = [
                    'handler' => IdeHandle::makeFileHandler($viewPath, '1'),
                    'path'    => (string) Str::of($viewPath)->replace(config('livewire.view_path') . '/', ''),
                    'line'    => 1,
                ];
                $data['viewPath']    = (string) Str::of($viewPath)->replace(config('livewire.view_path') . '/', '');
                $data['component']   = get_class($component);
                $data['id']          = $component->id;
                $data['dateTime']    = now()->format('H:i:s');

                $dumps = new LaraDumps(notificationId: $data['view']);

                $dumps->send(new LivewirePayload($data));

                $dumps->toScreen('Livewire');
            });
        }
    }

    private function getViewPath(View $view): string
    {
        $reflection = new ReflectionClass($view);
        $property   = $reflection->getProperty('path');
        $property->setAccessible(true);

        return strval($property->getValue($view));
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_livewire_components');
    }
}
