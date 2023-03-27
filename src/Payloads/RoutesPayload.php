<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Routing\Route;
use Illuminate\Support\{Arr, Str};
use LaraDumps\LaraDumps\Actions\GetRoutesToIgnore;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

class RoutesPayload extends Payload
{
    public function __construct(
        private mixed $except
    ) {
    }

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

            foreach ($this->getAllExcepts() as $except) {
                if (Str::contains($route->uri, $except)) {
                    $ignore = true;
                }
            }

            if (!$ignore) {
                $routes[] = [
                    'method' => implode('|', $route->methods),
                    'name'   => $route->getName() ?? '',
                    'uri'    => $route->uri,
                    'action' => $route->getActionName(),
                ];
            }
        };

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
            'label' => 'Routes',
        ];
    }

    public function getAllExcepts(): array
    {
        return array_merge(
            (array) GetRoutesToIgnore::handle(),
            Arr::wrap($this->except),
        );
    }
}
