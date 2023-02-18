<?php

namespace LaraDumps\LaraDumps\Actions;

use Illuminate\Support\Str;

final class MakeFileHandler
{
    public static function handle(array $trace, string $keyHandler = 'DS_FILE_HANDLER', string $forceProjectPath = 'DS_PROJECT_PATH'): string
    {
        if (empty($trace) || empty($trace['file'])) {
            return '';
        }

        if (empty($trace['line'])) {
            $trace['line'] = 1;
        }

        $keyHandler        = env($keyHandler, '');
        $forceProjectPath  = env($forceProjectPath, '');

        if (!is_string($keyHandler) || !is_string($forceProjectPath)) {
            return '';
        }

        $filename = basename($trace['file']);

        $filepath = Str::of($trace['file'])
            ->before($filename)
            ->toString();

        if (!empty($forceProjectPath)) {
            $filepath = str_replace($filepath, $forceProjectPath, $filepath);
        }

        $filepath = self::endsWithSeparator($filepath);

        return Str::of($keyHandler)
                ->replace('{filepath}', $filepath . $filename)
                ->replace('{line}', $trace['line'])
                ->toString();
    }

    protected static function endsWithSeparator(string $filepath): string
    {
        $separator = '';

        if (str_contains($filepath, '/')) {
            $separator = '/';
        }
        if (str_contains($filepath, '\\')) {
            $separator = '\\';
        }

        if (substr($filepath, -1) !== $separator) {
            $filepath .= $separator;
        }

        return $filepath;
    }
}
