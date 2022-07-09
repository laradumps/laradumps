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
                $dumps->toScreen(<<<HTML
<div class="w-full flex justify-between items-center space-x-2">
<span class="w-[1rem]">
    <svg viewBox="0 0 32 32" class="w-[1rem]"><g><g id="Error_1_"><g id="Error"><circle cx="16" cy="16" id="BG" r="16" style="fill:#D72828;"/><path d="M14.5,25h3v-3h-3V25z M14.5,6v13h3V6H14.5z" id="Exclamatory_x5F_Sign" style="fill:#E6E6E6;"/></g></g></g></svg>
</span>
<span>
    Failed Validation
</span>
</div>
HTML, raiseIn: intval(config('laradumps.send_livewire_failed_validation.sleep')));
            });
        }
    }

    public function isEnabled(): bool
    {
        return (bool) config('laradumps.send_livewire_failed_validation.enabled');
    }
}
