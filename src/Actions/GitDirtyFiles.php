<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Code snippet taken from laravel/pint
 * https://github.com/laravel/pint/pull/130
 */
class GitDirtyFiles
{
    public static function run()
    {
        $process = tap(new Process(['git', 'status', '--short', '--', '*.php']))->run();

        if (!$process->isSuccessful()) {
            abort(1);
        }

        return collect(preg_split('/\R+/', $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY))
            ->mapWithKeys(fn ($file) => [substr($file, 3) => trim(substr($file, 0, 3))])
            ->reject(fn ($status) => $status === 'D')
            ->map(fn ($status, $file) => $status === 'R' ? Str::after($file, ' -> ') : $file)
            ->map(fn ($file) => getcwd() . '/' . $file)
            ->values()
            ->all();
    }
}
