<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;

final class GetDebugFunctons
{
    public static function handle(string $key = 'DS_DEBUG_FUNCTIONS'): array
    {
        $functions = (string) env($key, '');

        if (empty($functions)) {
            return [];
        }

        return Str::of($functions)
                ->rtrim('(')
                ->rtrim('()')
                ->explode(',')
                ->map(fn ($function) => rtrim(rtrim(trim($function), '('), '()') . '(')
                ->toArray();
    }
}
