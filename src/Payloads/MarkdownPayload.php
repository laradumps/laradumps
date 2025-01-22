<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\Markdown;
use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

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

    public function screen(): array|Screen
    {
        return [];
    }

    public function label(): array|Label
    {
        return [];
    }
}
