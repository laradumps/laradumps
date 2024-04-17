<?php

namespace LaraDumps\LaraDumps\Livewire\Attributes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Ds extends \Livewire\Attribute
{
    protected static array $profiles = [];

    public function boot(): void
    {
        if (app()->isProduction()) {
            return;
        }

        DB::enableQueryLog();

        \Livewire\on('profile', function (string $method, string $livewireId, $measurement) {
            if ($livewireId != $this->getComponent()->getId()) {
                return;
            }

            $startedAt = $measurement[0];
            $endedAt   = $measurement[1];

            static::$profiles[$livewireId][] = [
                'classes'  => $this->matchClass($method),
                'method'   => $method,
                'duration' => $this->duration($startedAt, $endedAt),
            ];
        });

        \Livewire\on('dehydrate', function (Component $component, ComponentContext $context) {
            if ($component->getId() == $this->getComponent()->getId()) {
                $size = Number::fileSize(
                    strlen(
                        (string) json_encode([$component, $context])
                    )
                );

                $properties = $context->component->all();
                $errors     = $context->memo['errors'] ?? [];
                $events     = $context->effects['dispatches'] ?? [];

                $payload = [
                    'queries'    => DB::getQueryLog(),
                    'request'    => uniqid(),
                    'id'         => $context->component->getId(),
                    'name'       => $context->component->getName(),
                    'profile'    => static::$profiles[$context->component->getId()],
                    'properties' => Dumper::dump($properties),
                    'errors'     => filled($errors) ? Dumper::dump($errors) : [],
                    'events'     => $events,
                    'size'       => $size,
                ];

                $payload = new LivewirePayload($payload);

                $laradumps = app(LaraDumps::class);
                $laradumps->send($payload);
                $laradumps->toScreen('Livewire');

                unset(static::$profiles[$context->component->getId()]);

                DB::disableQueryLog();
            }
        });
    }

    private function duration(float $startTime, float $endTime): float
    {
        return round((($endTime - $startTime) * 1000));
    }

    private function matchClass(string $method): string
    {
        return match ($method) {
            'mount'  => 'border-l-4 border-primary',
            'render' => 'border-l-4 border-secondary',
            'hydrate', 'dehydrate' => 'border-l-4 border-accent',
            default => 'border-l-4 border-info'
        };
    }
}

class LivewirePayload extends Payload
{
    public function __construct(
        public array $payload
    ) {
    }

    public function type(): string
    {
        return 'livewire';
    }

    public function content(): array
    {
        return $this->payload;
    }
}
