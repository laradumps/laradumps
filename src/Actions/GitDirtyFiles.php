<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

final class GitDirtyFiles
{
    /**
     * @internal
     * Code snippet taken from laravel/pint
     * url: https://github.com/laravel/pint/pull/130
     */
    public static function run(): array
    {
        $process = tap(new Process(['git', 'status', '--short', '--', '*.php']))->run();

        if (!$process->isSuccessful()) {
            return [];
        }

        return collect((array) preg_split('/\R+/', $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY))
            ->mapWithKeys(fn ($file) => [substr(strval($file), 3) => trim(substr(strval($file), 0, 3))])
            ->reject(fn ($status) => $status === 'D')
            ->map(fn ($status, $file) => $status === 'R' ? Str::after($file, ' -> ') : $file)
            ->map(fn ($file) => getcwd() . '/' . $file)
            ->values()
            ->all();
    }
}
