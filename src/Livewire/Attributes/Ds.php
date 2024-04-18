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
    public function __construct(
        public bool $queries = true,
    ) {
    }

    protected static array $profilesBag = [];

    public function boot(): void
    {
        if ($this->queries) {
            DB::enableQueryLog();
        }

        \Livewire\on('profile', function (string $method, string $livewireId, array $measurement) {
            if ($livewireId != $this->getComponent()->getId()) {
                return;
            }

            $startedAt = $measurement[0];
            $endedAt   = $measurement[1];

            static::$profilesBag[$livewireId][] = [
                'classes'  => $this->matchClass($method),
                'method'   => $method,
                'duration' => $this->duration($startedAt, $endedAt),
            ];
        });

        \Livewire\on('dehydrate', function (Component $component, ComponentContext $context) {
            if ($component->getId() != $this->getComponent()->getId()) {
                return;
            }

            $size = Number::fileSize(
                strlen(
                    (string) json_encode([$component, $context])
                )
            );

            $properties = $context->component->all();
            $errors     = $context->memo['errors'] ?? [];
            $events     = $context->effects['dispatches'] ?? [];

            $payload = [
                'request'    => uniqid(),
                'id'         => $context->component->getId(),
                'name'       => $context->component->getName(),
                'profile'    => static::$profilesBag[$context->component->getId()],
                'properties' => Dumper::dump($properties),
                'errors'     => filled($errors) ? Dumper::dump($errors) : [],
                'queries'    => $this->queries ? DB::getQueryLog() : [],
                'events'     => $events,
                'size'       => $size,
            ];

            $laradumps = app(LaraDumps::class);

            $payload = new LivewirePayload($payload);

            $laradumps->send($payload);
            $laradumps->toScreen('Livewire');

            unset(static::$profilesBag[$context->component->getId()]);

            if ($this->queries) {
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
            'mount'  => 'border-primary',
            'render' => 'border-secondary',
            'hydrate', 'dehydrate' => 'border-accent',
            default => 'border-neutral'
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
