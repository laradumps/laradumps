<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumps\Support;
use LaraDumps\LaraDumps\Support\IdeHandle;

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
            'dump' => Support\Dumper::dump($this->dump),
        ];
    }

    public function customHandle(): array
    {
        return [
            'handler' => IdeHandle::makeFileHandler($this->viewPath, '1'),
            'path'    => $this->viewPath,
            'line'    => 1,
        ];
    }
}
