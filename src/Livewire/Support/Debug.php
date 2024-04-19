<?php

namespace LaraDumps\LaraDumps\Livewire\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use LaraDumps\LaraDumps\LaraDumps;
use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\{ComponentContext, HandleComponents};

use function Livewire\{invade, on};

class Debug
{
    protected static array $profilesBag = [];

    protected static array $components = [];

    public static function debug(string $componentId, bool $queriesEnabled = true): void
    {
        if ($queriesEnabled) {
            DB::enableQueryLog();
        }

        on('dehydrate', function (Component $component, ComponentContext $context) use ($componentId, $queriesEnabled) {
            if ($component->getId() != $componentId) {
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
                'profile'    => static::getProfileOrdered($component->getId()),
                'properties' => Dumper::dump($properties),
                'errors'     => filled($errors) ? Dumper::dump($errors) : [],
                'queries'    => DB::getQueryLog(),
                'events'     => $events,
                'size'       => $size,
            ];

            $laradumps = app(LaraDumps::class);

            $payload = new class ($payload) extends Payload {
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
            };

            $laradumps->send($payload);
            $laradumps->toScreen('Livewire');

            unset(static::$profilesBag[$context->component->getId()]);
            static::$components = [];

            if ($queriesEnabled) {
                DB::disableQueryLog();
            }
        });

        on('profile', function (string $method, string $livewireId, array $measurement) use ($componentId) {
            /** @var \ReflectionClass $register */
            $register       = invade(HandleComponents::class)->reflected;
            $componentStack = $register->getProperty('componentStack');

            collect((array) $componentStack->getValue(app(HandleComponents::class)))
                /** @phpstan-ignore-next-line  */
                ->each(function (Component $component) {
                    if (collect(static::$components)
                        ->where('id', $component->getId())->isEmpty()) {
                        static::$components[] = [
                            'id'   => $component->getId(),
                            'name' => $component->getName(),
                        ];
                    }
                });

            if ($livewireId != $componentId) {
                return;
            }

            $startedAt = $measurement[0];
            $endedAt   = $measurement[1];

            if (str($method)->startsWith('child:')) {
                $childId = str($method)->after('child:')->toString();

                $childName = collect(static::$components)
                    ->where('id', $childId)
                    ->first();

                $method = 'child:' . $childName['name'];
            }

            static::$profilesBag[$livewireId][] = [
                'classes'         => self::matchClass($method),
                'graphic_classes' => self::matchGraphicClass($method),
                'method'          => $method,
                'duration'        => self::duration($startedAt, $endedAt),
            ];
        });
    }

    public static function duration(float $startTime, float $endTime): float
    {
        return round((($endTime - $startTime) * 1000));
    }

    public static function getProfileOrdered(string $id): array
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

    public static function matchClass(string $method): string
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

    private static function matchGraphicClass(string $method): string
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
