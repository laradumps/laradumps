<?php

namespace LaraDumps\LaraDumps\Actions;

class IgnoreRoutePattern
{
    public static function execute(): bool
    {
        $patterns = config('laradumps.queries.ignore_routes_patterns', []);

        if (blank($patterns)) {
            return false;
        }

        $request = request();

        $path = ltrim($request->getPathInfo(), '/');

        if ($path === '') {
            return false;
        }

        return PatternMatcher::any($patterns, $path, true);
    }
}
