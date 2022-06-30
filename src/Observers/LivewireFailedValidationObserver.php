<?php

namespace LaraDumps\LaraDumps\Observers;

use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumps\Payloads\{LivewirePayload, TablePayload};
use LaraDumps\LaraDumps\Support\{Dumper, IdeHandle};
use ReflectionClass;

class LivewireFailedValidationObserver
{
    public function register(): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::listen('failed-validation', function (Validator $validator, $component) {
                $failedRules = [];

                foreach ($validator->getMessageBag()->messages() as $rule => $messages) {
                    foreach ($messages as $message) {
                        $data['property']   = $rule;
                        $data['message']    = $message;
                        $failedRules[]      = $data;
                    }
                }

                $notificationId = Str::of(strval(get_class($component)))->replace('\\', '-') . '-failed-validation';

                $reflectionClass = new ReflectionClass($component);

                $dumps = new LaraDumps(notificationId: strtolower($notificationId), backtrace: [
                    'file' => $reflectionClass->getFileName(),
                    'line' => 1,
                ]);

                $dumps->send(new TablePayload(collect($failedRules), strval(get_class($component))));
                $dumps->danger();
                $dumps->toScreen('Failed Validation', raiseIn: intval(config('laradumps.send_livewire_failed_validation.sleep')));
            });
        }
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_livewire_failed_validation.enabled');
    }
}
