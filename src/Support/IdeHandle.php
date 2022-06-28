<?php

namespace LaraDumps\LaraDumps\Support;

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

        if (str_contains($file, 'Laravel Kit')) {
            $fileHandle       = '';
            $file             = 'Laravel Kit';
            $line             = '';
        }

        if (str_contains($file, 'eval()')) {
            $fileHandle       = '';
            $file             = 'Tinker';
            $line             = '';
        }

        $file = str_replace(base_path() . '/', '', strval($file));

        if (str_contains($file, 'resources')) {
            $file = str_replace('resources/views/', '', strval($file));
        }

        return [
            'handler' => $fileHandle,
            'path'    => $file,
            'line'    => $line,
        ];
    }

    public static function makeFileHandler(?string $file, ?string $line): string
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
