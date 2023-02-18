<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;

final class GetCheckInDir
{
    public static function handle(string $key = 'DS_CHECK_IN_DIR'): array
    {
        $folders = env($key, '');

        if (!is_string($folders) || empty($folders)) {
            return [];
        }

        return Str::of($folders)
                ->rtrim(',')
                ->replaceMatches('/[\n\r]/', '')
                ->replaceMatches('/\s+/', '')
                ->explode(',')
                ->map(function ($folder) {
                    $folder = base_path(ltrim(rtrim('' . $folder, '/'), '/'));
                    $folder = str_replace('/', DIRECTORY_SEPARATOR, $folder);
                    $folder = str_replace('//', '/', $folder);
                    $folder = str_replace('\\\\', '\\', $folder);

                    return $folder;
                })
                ->toArray();
    }
}
