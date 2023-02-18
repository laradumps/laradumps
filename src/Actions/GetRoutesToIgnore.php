<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;

final class GetRoutesToIgnore
{
    public static function handle(string $key = 'DS_IGNORE_ROUTES'): array
    {
        $routes = env($key, '');

        if (!is_string($routes) || empty($routes)) {
            return [];
        }

        return Str::of($routes)
                ->rtrim(',')
                ->replaceMatches('/[\n\r]/', '')
                ->replaceMatches('/\s+/', '')
                ->explode(',')
                ->map(fn ($route) => trim('' . $route))
                ->toArray();
    }
}
