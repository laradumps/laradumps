<?php

namespace LaraDumps\LaraDumps\Support;

use LaraDumps\LaraDumps\Actions\{Config, MakeFileHandler};

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

        $fileHandle = MakeFileHandler::handle($this->trace);

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
}
