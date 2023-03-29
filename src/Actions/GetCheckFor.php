<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;

final class GetCheckFor
{
    public static function handle(string $key = 'DS_CHECK_FOR'): array
    {
        $functions = [];
        $checkFor  = env($key, '');

        if (!is_string($checkFor) || empty($checkFor)) {
            return [];
        }

        Str::of($checkFor)
                ->rtrim(',')
                ->replaceMatches('/[\n\r]/', '')
                ->replaceMatches('/\s+/', '')
                ->replace('(', '')
                ->replace(')', '')
                ->replace('@', '')
                ->replace('//', '')
                ->replace('->', '')
                ->explode(',')
                ->each(function ($item) use (&$functions) {
                    $functions[] = '@' . $item;
                    $functions[] = '->' . $item;
                    $functions[] = '//' . $item;
                    $functions[] = $item . '(';
                });

        return $functions;
    }
}
