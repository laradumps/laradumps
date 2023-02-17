<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;

final class GetFoldersToCheck
{
    public static function handle(string $key = 'DS_SEARCH_DEBUG_IN'): array
    {
        $folders = (string) env($key, '');

        if (empty($folders)) {
            return [];
        }

        return Str::of($folders)
                ->explode(',')
                ->map(fn ($folder) => base_path(ltrim(rtrim($folder, '/')), '/'))
                ->map(fn ($folder) => str_replace('/', DIRECTORY_SEPARATOR, $folder))
                ->toArray();
    }
}
