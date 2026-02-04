<?php

namespace LaraDumps\LaraDumps\Payloads;

use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class LogPayload extends Payload
{
    public function __construct(
        protected array $value,
    ) {
        $this->setOriginalContent($this->value['original_content'] ?? '');
    }

    public function type(): string
    {
        return 'log_application';
    }

    public function content(): array
    {
        return $this->value;
    }

    public function toScreen(): array|Screen
    {
        return new Screen('logs');
    }

    public function withLabel(): array|Label
    {
        return [];
    }
}
