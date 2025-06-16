<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Support\Facades\Context;
use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};

class ContextPayload extends Payload
{
    public function __construct(
        public string|array $keys,
    ) {}

    public function type(): string
    {
        return 'context';
    }

    public function content(): array
    {
        $keys = isset($this->keys[0]) && is_array($this->keys[0]) ? $this->keys[0] : $this->keys;

        return [
            'context' => count($keys)
                ? Context::only($keys)
                : Context::all(),
        ];
    }

    public function toScreen(): array|Screen
    {
        return new Screen('home');
    }

    public function withLabel(): array|Label
    {
        return ['Dumped Context'];
    }
}
