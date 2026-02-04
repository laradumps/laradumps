<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Actions\Dumper;
use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class BladePayload extends Payload
{
    public function __construct(
        public mixed $dump,
    ) {
        [$pre, $id] = Dumper::dump($this->dump);
        $this->setDumpId($id);
        $this->setOriginalContent($this->dump);

        $this->dump = $pre;
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump' => $this->dump,
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('home');
    }

    public function withLabel(): array|Label
    {
        return [];
    }
}
