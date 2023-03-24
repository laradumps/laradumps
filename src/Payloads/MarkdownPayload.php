<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\Markdown;
use LaraDumps\LaraDumpsCore\Payloads\Payload;

class MarkdownPayload extends Payload
{
    public function __construct(
        public string $dump
    ) {
    }

    public function type(): string
    {
        return 'dump';
    }

    public function content(): array
    {
        return [
            'dump' => Markdown::parse($this->dump)->toHtml(),
        ];
    }
}
