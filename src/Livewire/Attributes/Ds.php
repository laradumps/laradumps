<?php

namespace LaraDumps\LaraDumps\Livewire\Attributes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\LaraDumps;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Livewire\Component;

use function Livewire\invade;

use Livewire\Mechanisms\HandleComponents\{ComponentContext, HandleComponents};

use ReflectionClass;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Ds extends \Livewire\Attribute
{
    protected static array $profilesBag = [];

    protected static array $components = [];

    public function __construct(
        public bool $queries = true,
    ) {
    }

    public function boot(): void
    {
        if ($this->queries) {
            DB::enableQueryLog();
        }

        \Livewire\on('profile', function (string $method, string $livewireId, array $measurement) {
            /** @var ReflectionClass $register */
            $register         = invade(HandleComponents::class)->reflected;
            $handleComponents = $register->getProperty('componentStack');

            /** @var array $componentStack */
            $componentStack = $handleComponents->getValue(app(HandleComponents::class));

            foreach ($componentStack as $component) {
                if (collect(static::$components)->where('id', $component->getId())->isEmpty()) {
                    static::$components[] = [
                        'id'   => $component->getId(),
                        'name' => $component->getName(),
                    ];
                }
            }

            if ($livewireId != $this->getComponent()->getId()) {
                return;
            }

            $startedAt = $measurement[0];
            $endedAt   = $measurement[1];

            if (str($method)->startsWith('child:')) {
                $childId = str($method)->after('child:')->toString();

                $childName = collect(static::$components)
                    ->where('id', $childId)
                    ->first()['name'];

                $method = 'child:' . $childName;
            }

            static::$profilesBag[$livewireId][] = [
                'classes'         => $this->matchClass($method),
                'graphic_classes' => $this->matchGraphicClass($method),
                'method'          => $method,
                'duration'        => $this->duration($startedAt, $endedAt),
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
                'components' => static::$components,
                'id'         => $context->component->getId(),
                'name'       => $context->component->getName(),
                'profile'    => $this->getProfileOrdered($component->getId()),
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
            static::$components = [];

            if ($this->queries) {
                DB::disableQueryLog();
            }
        });
    }

    private function duration(float $startTime, float $endTime): float
    {
        return round((($endTime - $startTime) * 1000));
    }

    private function getProfileOrdered(string $id): array
    {
        $newProfiles = [];
        $profiles    = collect((array) static::$profilesBag[$id]);

        $newProfiles['mount']   = $profiles->where('method', 'mount')->first() ?? [];
        $newProfiles['hydrate'] = $profiles->where('method', 'hydrate')->first() ?? [];

        $rest = $profiles->whereNotIn('method', ['mount', 'hydrate', 'render']);

        foreach ($rest as $restProfile) {
            $newProfiles[$restProfile['method']] = $restProfile;
        }

        $newProfiles['render'] = $profiles->where('method', 'render')->first() ?? [];

        return $newProfiles;
    }

    private function matchClass(string $method): string
    {
        if (str_contains($method, 'child:')) {
            return 'border-purple-500';
        }

        return match ($method) {
            'mount'  => 'border-blue-500',
            'render' => 'border-green-500',
            'hydrate', 'dehydrate' => 'border-orange-500',
            default => 'border-red-500'
        };
    }

    private function matchGraphicClass(string $method): string
    {
        if (str_contains($method, 'child:')) {
            return 'bg-purple-500';
        }

        return match ($method) {
            'mount'  => 'bg-blue-500',
            'render' => 'bg-green-500',
            'hydrate', 'dehydrate' => 'bg-orange-500',
            default => 'bg-red-500'
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
