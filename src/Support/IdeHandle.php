<?php

namespace LaraDumps\LaraDumps\Support;

use Illuminate\Support\Str;

class IdeHandle
{
    public function __construct(
        public array $backtrace = [],
    ) {
    }

    public function ideHandle(): array
    {
        $file = $this->backtrace['file'];
        $line = $this->backtrace['line'];

        $fileHandle = $this->makeFileHandler($file, $line);

        if (Str::contains($file, 'Laravel Kit')) {
            $fileHandle       = '';
            $file             = 'Laravel Kit';
            $line             = '';
        }

        if (Str::contains($file, 'eval()')) {
            $fileHandle       = '';
            $file             = 'Tinker';
            $line             = '';
        }

        $file = str_replace(base_path() . '/', '', strval($file));

        return [
            'handler' => $fileHandle,
            'path'    => $file,
            'line'    => $line,
        ];
    }

    public static function makeFileHandler(string $file, string $line): string
    {
        /** @var string $preferredIde */
        $preferredIde = config('laradumps.preferred_ide');
        /** @var array $handlers */
        $handlers      = config('laradumps.ide_handlers');

        $ide           = $handlers[$preferredIde] ?? $handlers['vscode'];

        if (!empty($ide['line_separator'])) {
            $line = $ide['line_separator'] . $line;
        }

        return $ide['handler'] . $file . $line;
    }
}
