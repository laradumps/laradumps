<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Actions\MakeFileHandler;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use LaraDumps\LaraDumpsCore\Support\Dumper;

class BladePayload extends Payload
{
    public function __construct(
        public mixed $dump,
        public string $viewPath,
    ) {
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump' => Dumper::dump($this->dump),
        ];
    }

    public function customHandle(): array
    {
        return [
            'handler' => MakeFileHandler::handle(['file' => $this->viewPath, 'line' => 1]),
            'path'    => $this->viewPath,
            'line'    => 1,
        ];
    }
}
