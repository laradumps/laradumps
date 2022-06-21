<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\LivewirePayload;
use LaraDumps\LaraDumps\Support\{Dumper, IdeHandle};
use ReflectionClass;

class LivewireObserver
{
    public function register(): void
    {
        if (class_exists(\Livewire\Component::class)) {
            \Livewire\Livewire::listen('view:render', function (View $view) {
                if (!$this->isEnabled()) {
                    return;
                }
                /** @var \Livewire\Component $component */
                $component = $view->getData()['_instance'];

                if (in_array(get_class($component), (array) (config('laradumps.ignore_livewire_components')))) {
                    return;
                }

                $data = [
                    'data' => Dumper::dump($component->getPublicPropertiesDefinedBySubClass()),
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
