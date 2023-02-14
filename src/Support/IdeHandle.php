<?php

namespace LaraDumps\LaraDumps\Support;

use LaraDumps\LaraDumps\Actions\Config;

class IdeHandle
{
    public function __construct(
        public array $trace = [],
    ) {
    }

    public function ideHandle(): array
    {
        $file = strval(data_get($this->trace, 'file', ''));

        $line = strval(data_get($this->trace, 'line', ''));

        $fileHandle = $this->makeFileHandler($file, $line);

        if (str_contains($file, 'Laravel Kit')) {
            $fileHandle = '';
            $file       = 'Laravel Kit';
            $line       = '';
        }

        if (str_contains($file, 'eval()')) {
            $fileHandle = '';
            $file       = 'Tinker';
            $line       = '';
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

    public static function makeFileHandler(?string $file, string|int|null $line): string
    {
        /** @var string $preferredIde */
        $preferredIde = Config::get('preferred_ide');

        $configFile = include __DIR__ . '/../../config/laradumps.php';
        /** @var array $handlers */
        $handlers   = $configFile['ide_handlers'];

        $ide        = $handlers[$preferredIde] ?? $handlers['vscode'];
        $localPath  = $ide['local_path']       ?? null;
        $remotePath = $ide['remote_path']      ?? null;
        $workDir    = $ide['work_dir']         ?? null;

        if (!empty($localPath)) {
            $file      = $localPath . $file;
        }

        if (!empty($remotePath)) {
            $file = str_replace($workDir, $remotePath, strval($file));
        }

        if (!empty($ide['line_separator'])) {
            $line = $ide['line_separator'] . $line;
        }

        return $ide['handler'] . $file . $line;
    }
}
