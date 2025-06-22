<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Routing\Route;
use Illuminate\Support\{Arr, Str};
use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class RoutesPayload extends Payload
{
    public function __construct(
        private mixed $except
    ) {}

    public function type(): string
    {
        return 'table';
    }

    /** @return array<string, array<int, array<string, string|null>|string>|string> */
    public function content(): array
    {
        $routes = [];

        /** @var Route $route */
        /** @phpstan-ignore-next-line */
        foreach (\Route::getRoutes()->getIterator() as $route) {
            $ignore = false;

            /** @var string $except */
            foreach (Arr::wrap($this->except) as $except) {
                if (Str::contains($route->uri, $except)) {
                    $ignore = true;
                }
            }

            if (! $ignore) {
                $routes[] = [
                    'method' => implode('|', $route->methods),
                    'name' => $route->getName() ?? '',
                    'uri' => $route->uri,
                    'action' => $route->getActionName(),
                ];
            }
        }

        return [
            'fields' => [
                'method',
                'name',
                'uri',
                'action',
            ],
            'values' => $routes,
            'header' => [
                'Method',
                'Name',
                'Uri',
                'Action',
            ],
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('home');
    }

    public function withLabel(): array|Label
    {
        return new Label('Routes');
    }
}
